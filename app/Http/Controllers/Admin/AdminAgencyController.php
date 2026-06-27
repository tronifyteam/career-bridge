<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminAgencyController extends Controller
{
    public function index(Request $request)
    {
        $query = User::whereIn('role', ['agency', 'agency_staff'])
            ->with(['documents', 'agencyStaff'])
            ->withCount('jobs')
            ->orderByDesc('created_at');

        if ($search = $request->get('search')) {
            $query->where(fn($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('company_name', 'like', "%{$search}%")
                ->orWhere('unified_business_number', 'like', "%{$search}%")
                ->orWhere('license_number', 'like', "%{$search}%")
            );
        }
        if ($role = $request->get('role')) {
            $query->where('role', $role);
        }
        if ($vs = $request->get('verification_status')) {
            $query->where('verification_status', $vs);
        }
        if ($bs = $request->get('badge_status')) {
            $query->where('verified_badge_status', $bs);
        }

        $agencies = $query->paginate(20);
        return view('admin.agencies.index', compact('agencies'));
    }

    public function show(Request $request, $id)
    {
        $agency = User::find($id);
        if (! $agency || !in_array($agency->role, ['agency', 'agency_staff'])) {
            abort(404);
        }

        $documents = $agency->documents()->orderByDesc('created_at')->get();
        $logs = \App\Models\VerificationLog::where('entity_type', 'employer')
                    ->where('entity_id', $agency->id)
                    ->with('verifiedBy')
                    ->latest('verified_at')
                    ->take(20)
                    ->get();
                    
        // If it's a parent agency, get their staff
        $staffMembers = collect();
        if ($agency->role === 'agency') {
            $staffMembers = \App\Models\EmployerStaff::where('agency_employer_id', $agency->id)
                ->with('user')
                ->get();
        }

        return view('admin.agencies.show', compact('agency', 'documents', 'logs', 'staffMembers'));
    }
}
