<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('verification_status')) {
            $query->where('verification_status', $request->verification_status);
        }

        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(full_name) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(email) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(company_name) LIKE ?', ["%{$search}%"]);
            });
        }

        $users = $query->orderByDesc('created_at')->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function show(User $user)
    {
        $user->load(['jobs', 'applications.job', 'documents', 'workerDocuments']);
        return view('admin.users.show', compact('user'));
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }

    public function updatePassword(Request $request, User $user)
    {
        $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user->update([
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
        ]);

        return redirect()->back()->with('success', 'User password updated successfully.');
    }

    public function updateVerification(Request $request, User $user)
    {
        $request->validate([
            'verification_status' => 'required|in:unverified,pending,basic_verified,manually_verified,rejected',
            'verification_note' => 'nullable|string',
        ]);

        $updates = [
            'verification_status' => $request->verification_status,
            'verification_note' => $request->verification_note,
        ];

        // Auto update related document statuses and badges
        if (in_array($request->verification_status, ['basic_verified', 'manually_verified'])) {
            $updates['verified_badge_status'] = 'verified';
            $updates['verified_badge_updated_at'] = now();
            $updates['ready_to_work_status'] = 'ready';
            $updates['ready_to_work_updated_at'] = now();
            $user->documents()->where('status', 'pending')->update(['status' => 'approved']);
        } elseif ($request->verification_status === 'rejected') {
            $updates['verified_badge_status'] = 'rejected';
            $updates['verified_badge_updated_at'] = now();
            $updates['ready_to_work_status'] = 'rejected';
            $updates['ready_to_work_updated_at'] = now();
            $user->documents()->where('status', 'pending')->update(['status' => 'rejected']);
        }

        $user->update($updates);

        return redirect()->back()->with('success', 'User verification status updated successfully to ' . $request->verification_status . '.');
    }

    public function updateWorkerVerification(Request $request, User $user)
    {
        $request->validate([
            'verified_badge_status' => 'required|in:unverified,pending,verified,rejected',
            'ready_to_work_status' => 'required|in:not_ready,pending,ready,rejected',
            'sponsorship_status' => 'nullable|string|max:255',
            'verification_note' => 'nullable|string',
        ]);

        $user->update([
            'verified_badge_status' => $request->verified_badge_status,
            'ready_to_work_status' => $request->ready_to_work_status,
            'sponsorship_status' => $request->sponsorship_status,
            'verification_note' => $request->verification_note,
        ]);

        // Auto update related worker document statuses
        // Note: verified_badge_status enum is: unverified|pending|verified|rejected
        if ($request->verified_badge_status === 'verified') {
            $user->workerDocuments()->whereHas('documentType', fn($q) => $q->whereIn('slug', ['selfie', 'personal_id', 'personal_document']))->where('review_status', 'pending')->update(['review_status' => 'approved']);
        } elseif ($request->verified_badge_status === 'rejected') {
            $user->workerDocuments()->whereHas('documentType', fn($q) => $q->whereIn('slug', ['selfie', 'personal_id', 'personal_document']))->where('review_status', 'pending')->update(['review_status' => 'rejected']);
        }

        // Note: ready_to_work_status enum is: not_ready|pending|ready|rejected
        if ($request->ready_to_work_status === 'ready') {
            $user->workerDocuments()->whereHas('documentType', fn($q) => $q->whereNotIn('slug', ['selfie', 'personal_id', 'personal_document']))->where('review_status', 'pending')->update(['review_status' => 'approved']);
        } elseif ($request->ready_to_work_status === 'rejected') {
            $user->workerDocuments()->whereHas('documentType', fn($q) => $q->whereNotIn('slug', ['selfie', 'personal_id', 'personal_document']))->where('review_status', 'pending')->update(['review_status' => 'rejected']);
        }

        return redirect()->back()->with('success', 'Worker verification statuses updated successfully.');
    }
}
