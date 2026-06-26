<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use Illuminate\Http\Request;

class AdminAdvertisementController extends Controller
{
    public function index(Request $request)
    {
        $query = Advertisement::with(['user', 'package', 'job']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $advertisements = $query->orderByDesc('created_at')->paginate(20);

        return view('admin.advertisements.index', compact('advertisements'));
    }

    public function updateStatus(Request $request, Advertisement $advertisement)
    {
        $request->validate([
            'status' => 'required|in:active,paused,rejected,expired',
            'admin_note' => 'nullable|string'
        ]);

        $oldStatus = $advertisement->status;
        $advertisement->status = $request->status;
        if ($request->filled('admin_note')) {
            $advertisement->admin_note = $request->admin_note;
        }

        // If activating a sponsored job, update the job itself
        if ($advertisement->type === 'sponsored_job' && $advertisement->job_id) {
            if ($request->status === 'active' && $oldStatus !== 'active') {
                // Only sponsor the job immediately if it's already time to start
                if (!$advertisement->starts_at || $advertisement->starts_at <= now()) {
                    $advertisement->job->update([
                        'is_sponsored' => true,
                        'sponsored_until' => $advertisement->expires_at
                    ]);
                }
            } elseif (in_array($request->status, ['paused', 'rejected', 'expired']) && $oldStatus === 'active') {
                $advertisement->job->update([
                    'is_sponsored' => false
                ]);
            }
        }

        $advertisement->save();

        \App\Services\AuditLogService::log(
            'update_ad_status',
            "Updated advertisement #{$advertisement->id} status from {$oldStatus} to {$request->status}",
            ['ad_id' => $advertisement->id, 'old_status' => $oldStatus, 'new_status' => $request->status, 'admin_note' => $request->admin_note]
        );

        return back()->with('success', 'Advertisement status updated to ' . $request->status);
    }
}
