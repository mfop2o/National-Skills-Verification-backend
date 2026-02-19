<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Institution;
use App\Models\AuditLog;
use App\Models\Verification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('role:admin');
    }

    public function dashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'total_institutions' => User::where('role', 'institution')->count(),
            'pending_institutions' => Institution::where('approval_status', 'pending')->count(),
            'total_verifications' => Verification::count(),
            'pending_verifications' => Verification::where('status', 'pending')->count(),
            'total_badges' => \App\Models\Badge::count(),
            'active_badges' => \App\Models\Badge::where('status', 'active')->count(),
            'recent_activities' => AuditLog::with('user')
                ->latest()
                ->take(10)
                ->get(),
        ];

        return response()->json($stats);
    }

    public function institutions(Request $request)
    {
        $query = Institution::with('user')->latest();

        if ($request->has('status')) {
            $query->where('approval_status', $request->status);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $institutions = $query->paginate(20);

        return response()->json($institutions);
    }

    public function approveInstitution(Request $request, $id)
    {
        $institution = Institution::findOrFail($id);

        if ($institution->approval_status !== 'pending') {
            return response()->json(['message' => 'Institution already processed'], 400);
        }

        DB::transaction(function() use ($request, $institution) {
            $institution->approval_status = 'approved';
            $institution->approved_by = $request->user()->id;
            $institution->approved_at = now();
            $institution->save();

            // Update user status
            $user = $institution->user;
            $user->is_verified_institution = true;
            $user->status = 'active';
            $user->save();

            AuditLog::create([
                'user_id' => $request->user()->id,
                'action' => 'approve_institution',
                'entity_type' => 'institution',
                'entity_id' => $institution->id,
                'new_data' => ['institution_name' => $institution->institution_name],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
        });

        return response()->json(['message' => 'Institution approved successfully']);
    }

    public function rejectInstitution(Request $request, $id)
    {
        $validator = $request->validate([
            'reason' => 'required|string|min:10',
        ]);

        $institution = Institution::findOrFail($id);

        if ($institution->approval_status !== 'pending') {
            return response()->json(['message' => 'Institution already processed'], 400);
        }

        DB::transaction(function() use ($request, $institution) {
            $institution->approval_status = 'rejected';
            $institution->rejection_reason = $request->reason;
            $institution->approved_by = $request->user()->id;
            $institution->approved_at = now();
            $institution->save();

            // Update user status
            $user = $institution->user;
            $user->status = 'rejected';
            $user->save();

            AuditLog::create([
                'user_id' => $request->user()->id,
                'action' => 'reject_institution',
                'entity_type' => 'institution',
                'entity_id' => $institution->id,
                'new_data' => ['reason' => $request->reason],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
        });

        return response()->json(['message' => 'Institution rejected']);
    }

    public function auditLogs(Request $request)
    {
        $query = AuditLog::with('user')->latest();

        if ($request->has('action')) {
            $query->where('action', $request->action);
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $logs = $query->paginate(50);

        return response()->json($logs);
    }

    public function suspendUser(Request $request, $id)
    {
        $validator = $request->validate([
            'reason' => 'required|string|min:10',
        ]);

        $user = User::findOrFail($id);

        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'Cannot suspend yourself'], 400);
        }

        $user->status = 'suspended';
        $user->save();

        // Revoke all tokens
        $user->tokens()->delete();

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'suspend_user',
            'entity_type' => 'user',
            'entity_id' => $user->id,
            'new_data' => ['reason' => $request->reason],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return response()->json(['message' => 'User suspended successfully']);
    }

    public function reactivateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $user->status = 'active';
        $user->save();

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'reactivate_user',
            'entity_type' => 'user',
            'entity_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return response()->json(['message' => 'User reactivated']);
    }
}