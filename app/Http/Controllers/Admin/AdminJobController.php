<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Services\JobScreeningService;
use Illuminate\Http\Request;

class AdminJobController extends Controller
{
    public function __construct(private JobScreeningService $screening) {}

    public function index(Request $request)
    {
        $query = Job::with('employer');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('risk_level')) {
            $query->where('risk_level', $request->risk_level);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('employer_type')) {
            $query->where('employer_type', $request->employer_type);
        }

        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(title) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(employer_name) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(location) LIKE ?', ["%{$search}%"]);
            });
        }

        $jobs = $query->withCount('applications')->orderByDesc('created_at')->paginate(20);

        // Stats for the top bar
        $stats = [
            'total'    => Job::count(),
            'pending'  => Job::where('status', 'submitted_for_review')->count(),
            'critical' => Job::where('risk_level', 'critical')->whereIn('status', ['submitted_for_review', 'suspended'])->count(),
            'high'     => Job::where('risk_level', 'high')->where('status', 'submitted_for_review')->count(),
        ];

        return view('admin.jobs.index', compact('jobs', 'stats'));
    }

    public function show(Job $job)
    {
        $job->load(['employer', 'applications.user']);

        // Run screening if never screened or job was re-submitted
        if (is_null($job->screened_at) && $job->status === 'submitted_for_review') {
            $this->screening->screenAndSave($job);
            $job->refresh();
        }

        return view('admin.jobs.show', compact('job'));
    }

    public function destroy(Job $job)
    {
        $job->delete();
        return redirect()->route('admin.jobs.index')->with('success', 'Job deleted successfully.');
    }

    public function updateStatus(Request $request, Job $job)
    {
        $request->validate([
            'status'           => 'required|in:draft,submitted_for_review,published,paused,closed,rejected,suspended',
            'rejection_reason' => 'nullable|string|max:1000',
        ]);

        if ($request->status === 'rejected' && empty($request->rejection_reason)) {
            return redirect()->back()
                ->withErrors(['rejection_reason' => 'Alasan penolakan wajib diisi jika status Ditolak.'])
                ->withInput();
        }

        $job->update([
            'status'           => $request->status,
            'rejection_reason' => in_array($request->status, ['rejected', 'suspended'])
                ? $request->rejection_reason
                : ($request->status === 'published' ? null : $job->rejection_reason),
            // Clear risk when approved (admin vouches for it)
            'risk_level'       => $request->status === 'published' ? 'low' : $job->risk_level,
        ]);

        // Auto-reject applications if job becomes inactive
        if (in_array($request->status, ['rejected', 'suspended', 'closed', 'paused'])) {
            $applications = \App\Models\JobApplication::where('job_id', $job->id)
                ->whereIn('status', ['pending', 'viewed', 'shortlisted'])
                ->get();

            foreach ($applications as $app) {
                $note = match ($request->status) {
                    'rejected', 'suspended' => 'Lowongan pekerjaan ini telah ditangguhkan oleh sistem.',
                    'closed' => 'Lowongan pekerjaan ini telah ditutup oleh majikan.',
                    'paused' => 'Lowongan pekerjaan ini sedang dihentikan sementara.',
                    default => 'Lowongan pekerjaan ini tidak lagi tersedia.'
                };

                $app->update([
                    'status'         => 'rejected',
                    'employer_notes' => $note,
                ]);

                \App\Models\ApplicationStatusLog::create([
                    'application_id' => $app->id,
                    'status'         => 'rejected',
                    'notes'          => $note,
                    'changed_by'     => 'system',
                    'changed_at'     => now(),
                ]);

                // Notify worker
                if ($app->user_id) {
                    \App\Models\AppNotification::create([
                        'user_id' => $app->user_id,
                        'type'    => 'application_status',
                        'title'   => 'Maaf, Lowongan Tidak Tersedia',
                        'body'    => "Lamaran Anda untuk posisi \"{$job->title}\" dibatalkan karena lowongan tidak lagi tersedia. ({$note})",
                        'data'    => ['job_id' => $job->id, 'application_id' => $app->id],
                    ]);
                }
            }
        }

        // Send Email Notification via Queue
        if (in_array($request->status, ['published', 'rejected']) && $job->employer && !empty($job->employer->email)) {
            try {
                \Illuminate\Support\Facades\Mail::to($job->employer->email)->queue(new \App\Mail\JobStatusUpdatedMail($job));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('JobStatusUpdatedMail failed: ' . $e->getMessage());
            }
        }

        return redirect()->back()->with('success', 'Job status updated to ' . $request->status . '.');
    }

    /**
     * Re-run auto-screening on a specific job.
     */
    public function rescreen(Job $job)
    {
        $this->screening->screenAndSave($job);
        return redirect()->back()->with('success', 'Job re-screened. Risk level updated.');
    }

    /**
     * Bulk screen all unscreened pending jobs.
     */
    public function bulkScreen()
    {
        $jobs = Job::where('status', 'submitted_for_review')
            ->whereNull('screened_at')
            ->limit(50)
            ->get();

        foreach ($jobs as $job) {
            $this->screening->screenAndSave($job);
        }

        return redirect()->route('admin.jobs.index')
            ->with('success', "Auto-screened {$jobs->count()} pending jobs.");
    }
}
