<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * GET /api/dashboard/worker
     */
    public function worker(Request $request): JsonResponse
    {
        $user = $request->user();

        $totalApplications = JobApplication::where('user_id', $user->id)->count();
        $pendingApplications = JobApplication::where('user_id', $user->id)->pending()->count();
        $acceptedApplications = JobApplication::where('user_id', $user->id)->accepted()->count();
        $totalJobs = Job::active()->count();
        $urgentJobs = Job::active()->urgent()->count();

        // Recent applications
        $recentApplications = JobApplication::with('job')
            ->where('user_id', $user->id)
            ->orderByDesc('applied_at')
            ->limit(5)
            ->get()
            ->map->toApiArray();

        // Recommended jobs (latest active jobs)
        $recommendedJobs = Job::active()
            ->orderByDesc('is_urgent')
            ->orderByDesc('posted_at')
            ->limit(5)
            ->get()
            ->map->toApiArray();

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => [
                    'total_applications' => $totalApplications,
                    'pending_applications' => $pendingApplications,
                    'accepted_applications' => $acceptedApplications,
                    'total_jobs_available' => $totalJobs,
                    'urgent_jobs' => $urgentJobs,
                ],
                'recent_applications' => $recentApplications,
                'recommended_jobs' => $recommendedJobs,
            ],
        ]);
    }

    /**
     * GET /api/dashboard/employer
     */
    public function employer(Request $request): JsonResponse
    {
        $user = $request->user();

        $totalJobs = Job::where('employer_id', $user->id)->count();
        $activeJobs = Job::where('employer_id', $user->id)->active()->count();
        $totalApplications = JobApplication::whereHas('job', fn ($q) => $q->where('employer_id', $user->id))->count();
        $pendingApplications = JobApplication::whereHas('job', fn ($q) => $q->where('employer_id', $user->id))->pending()->count();
        $hiredApplications = JobApplication::whereHas('job', fn ($q) => $q->where('employer_id', $user->id))->accepted()->count();

        // Recent applicants
        $recentApplicants = JobApplication::with(['job', 'user'])
            ->whereHas('job', fn ($q) => $q->where('employer_id', $user->id))
            ->orderByDesc('applied_at')
            ->limit(10)
            ->get()
            ->map->toApiArray();

        // My jobs with application counts
        $myJobs = Job::withCount('applications')
            ->where('employer_id', $user->id)
            ->orderByDesc('posted_at')
            ->limit(5)
            ->get()
            ->map->toApiArray();

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => [
                    'total_jobs' => $totalJobs,
                    'active_jobs' => $activeJobs,
                    'total_applications' => $totalApplications,
                    'pending_applications' => $pendingApplications,
                    'hired_applications' => $hiredApplications,
                ],
                'recent_applicants' => $recentApplicants,
                'my_jobs' => $myJobs,
            ],
        ]);
    }
}
