<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users'              => User::count(),
            'total_workers'            => User::workers()->count(),
            'total_employers'          => User::employers()->count(),
            'total_jobs'               => Job::count(),
            'active_jobs'              => Job::active()->count(),
            'total_applications'       => JobApplication::count(),
            'pending_applications'     => JobApplication::pending()->count(),
            // New: pending verifications
            'pending_worker_docs'      => \App\Models\WorkerDocument::where('review_status', 'pending')->count(),
            'pending_employer_docs'    => \App\Models\EmployerDocument::where('status', 'pending')->count(),
            'pending_employer_accounts'=> User::employers()->where('verification_status', 'unverified')->count(),
        ];

        $recentUsers        = User::orderByDesc('created_at')->limit(5)->get();
        $recentJobs         = Job::with('employer')->orderByDesc('created_at')->limit(5)->get();
        $recentApplications = JobApplication::with(['job', 'user'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentUsers', 'recentJobs', 'recentApplications'));
    }
}
