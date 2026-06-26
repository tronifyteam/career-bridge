<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use Illuminate\Http\Request;

class AdminApplicationController extends Controller
{
    public function index(Request $request)
    {
        $query = JobApplication::with(['job', 'user']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $applications = $query->orderByDesc('applied_at')->paginate(20);

        return view('admin.applications.index', compact('applications'));
    }

    public function updateStatus(Request $request, JobApplication $application)
    {
        $request->validate([
            'status' => 'required|in:pending,reviewed,accepted,rejected',
        ]);

        $application->update(['status' => $request->status]);

        return redirect()->back()->with('success', 'Application status updated.');
    }
}
