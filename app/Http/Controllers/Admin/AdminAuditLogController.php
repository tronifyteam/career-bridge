<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AdminAuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with(['admin']);

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('admin', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })->orWhere('description', 'like', "%{$search}%")
              ->orWhere('model_type', 'like', "%{$search}%");
        }

        $auditLogs = $query->orderByDesc('created_at')->paginate(20);

        return view('admin.audit_logs.index', compact('auditLogs'));
    }
}
