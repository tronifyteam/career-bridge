<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmployerStaff;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EmployerStaffController extends Controller
{
    /**
     * GET /api/employer/staff
     * List staff under the authenticated agency employer.
     */
    public function index(Request $request): JsonResponse
    {
        $employer = $request->user();

        if (! $employer->isAgency()) {
            return response()->json([
                'success' => false,
                'error'   => 'not_agency',
                'message' => 'Only agency employers can manage staff.',
            ], 403);
        }

        $staff = EmployerStaff::with('user')
            ->where('agency_employer_id', $employer->id)
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $staff->map->toApiArray()->values(),
        ]);
    }

    /**
     * POST /api/employer/staff/invite
     * Invite a user (by email) to join as agency staff.
     * The invited user must already be registered with role=agency_staff.
     */
    public function invite(Request $request): JsonResponse
    {
        $employer = $request->user();

        if (! $employer->isAgency()) {
            return response()->json([
                'success' => false,
                'error'   => 'not_agency',
                'message' => 'Only agency employers can invite staff.',
            ], 403);
        }

        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $invitee = User::where('email', $request->email)->first();

        if (! in_array($invitee->role, ['agency_staff', null, ''])) {
            return response()->json([
                'success' => false,
                'error'   => 'invalid_role',
                'message' => 'The invited user must have the agency_staff role.',
            ], 422);
        }

        if (EmployerStaff::where('user_id', $invitee->id)->exists()) {
            return response()->json([
                'success' => false,
                'error'   => 'already_staff',
                'message' => 'This user is already associated with an agency.',
            ], 422);
        }

        $staffRecord = EmployerStaff::create([
            'user_id'            => $invitee->id,
            'agency_employer_id' => $employer->id,
            'status'             => 'pending',
        ]);

        // Update invitee's role if not set
        if (! $invitee->role) {
            $invitee->update(['role' => 'agency_staff']);
        }

        return response()->json([
            'success' => true,
            'message' => "Invitation sent to {$invitee->email}. Awaiting approval.",
            'data'    => $staffRecord->load('user')->toApiArray(),
        ], 201);
    }

    /**
     * PUT /api/employer/staff/{id}/approve
     * Approve or reject a pending staff invitation.
     */
    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $employer    = $request->user();
        $staffRecord = EmployerStaff::where('agency_employer_id', $employer->id)->find($id);

        if (! $staffRecord) {
            return response()->json([
                'success' => false,
                'error'   => 'not_found',
                'message' => 'Staff record not found.',
            ], 404);
        }

        $request->validate([
            'status' => 'required|in:approved,rejected,suspended',
        ]);

        $staffRecord->update([
            'status'      => $request->status,
            'approved_at' => $request->status === 'approved' ? now() : null,
            'approved_by' => $request->status === 'approved' ? $employer->id : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Staff status updated to '{$request->status}'.",
            'data'    => $staffRecord->fresh()->load('user')->toApiArray(),
        ]);
    }

    /**
     * DELETE /api/employer/staff/{id}
     * Remove staff from the agency.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $employer    = $request->user();
        $staffRecord = EmployerStaff::where('agency_employer_id', $employer->id)->find($id);

        if (! $staffRecord) {
            return response()->json([
                'success' => false,
                'error'   => 'not_found',
                'message' => 'Staff record not found.',
            ], 404);
        }

        $staffRecord->delete();

        return response()->json([
            'success' => true,
            'message' => 'Staff removed from agency.',
        ]);
    }
}
