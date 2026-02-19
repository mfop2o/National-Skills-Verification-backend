<?php

namespace App\Http\Controllers;

use App\Models\Verification;
use App\Models\PortfolioItem;
use App\Models\Badge;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class VerificationController extends Controller
{
    public function queue(Request $request)
    {
        $this->authorize('viewAny', Verification::class);

        $verifications = Verification::where('institution_id', $request->user()->id)
            ->with(['portfolioItem.portfolio.user', 'portfolioItem' => function($q) {
                $q->withTrashed();
            }])
            ->whereIn('status', ['pending', 'in_review'])
            ->latest()
            ->paginate(20);

        return response()->json($verifications);
    }

    public function show(Verification $verification)
    {
        $this->authorize('view', $verification);

        $verification->load(['portfolioItem.portfolio.user', 'portfolioItem' => function($q) {
            $q->withTrashed();
        }]);

        return response()->json($verification);
    }

    public function startReview(Request $request, Verification $verification)
    {
        $this->authorize('update', $verification);

        if ($verification->status !== 'pending') {
            return response()->json(['message' => 'Verification cannot be started'], 400);
        }

        $verification->status = 'in_review';
        $verification->save();

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'start_verification_review',
            'entity_type' => 'verification',
            'entity_id' => $verification->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return response()->json($verification);
    }

    public function approve(Request $request, Verification $verification)
    {
        $this->authorize('update', $verification);

        $validator = Validator::make($request->all(), [
            'remarks' => 'nullable|string',
            'issue_badge' => 'boolean',
            'badge_name' => 'required_if:issue_badge,true|string',
            'badge_level' => 'nullable|in:beginner,intermediate,advanced,expert',
            'expires_at' => 'nullable|date|after:today',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::transaction(function() use ($request, $verification) {
            $verification->approve($request->user()->id, $request->remarks);

            // Issue badge if requested
            if ($request->issue_badge) {
                $badge = Badge::create([
                    'user_id' => $verification->portfolioItem->portfolio->user_id,
                    'issuer_id' => $request->user()->id,
                    'verification_id' => $verification->id,
                    'name' => $request->badge_name,
                    'skill_name' => $verification->portfolioItem->title,
                    'description' => $request->remarks,
                    'level' => $request->badge_level,
                    'issued_at' => now(),
                    'expires_at' => $request->expires_at,
                    'status' => 'active'
                ]);

                // Update user skill
                $user = $verification->portfolioItem->portfolio->user;
                $skillName = $verification->portfolioItem->title;
                
                // Find or create skill association
                // This would need a Skill model lookup
            }
        });

        return response()->json([
            'message' => 'Verification approved successfully',
            'verification' => $verification->fresh()
        ]);
    }

    public function reject(Request $request, Verification $verification)
    {
        $this->authorize('update', $verification);

        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string|min:10',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $verification->reject($request->user()->id, $request->rejection_reason);

        return response()->json([
            'message' => 'Verification rejected',
            'verification' => $verification->fresh()
        ]);
    }

    public function revoke(Request $request, Verification $verification)
    {
        $this->authorize('revoke', $verification);

        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|min:10',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($verification->status !== 'approved') {
            return response()->json(['message' => 'Only approved verifications can be revoked'], 400);
        }

        DB::transaction(function() use ($request, $verification) {
            $verification->status = 'revoked';
            $verification->remarks = $request->reason;
            $verification->save();

            // Revoke associated badge
            if ($verification->badge) {
                $verification->badge->revoke($request->reason);
            }

            // Update portfolio item
            $verification->portfolioItem->status = 'rejected';
            $verification->portfolioItem->save();

            AuditLog::create([
                'user_id' => $request->user()->id,
                'action' => 'revoke_verification',
                'entity_type' => 'verification',
                'entity_id' => $verification->id,
                'new_data' => ['reason' => $request->reason],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
        });

        return response()->json(['message' => 'Verification revoked']);
    }
}