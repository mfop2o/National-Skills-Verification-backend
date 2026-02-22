<?php

namespace App\Http\Controllers;

use App\Models\Verification;
use App\Models\Badge;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InstitutionDashboardController extends Controller
{
    public function index(Request $request)
    {
        $institution = $request->user();
        
        // Get verification stats
        $stats = [
            'total_verifications' => Verification::where('institution_id', $institution->id)->count(),
            'pending_count' => Verification::where('institution_id', $institution->id)->where('status', 'pending')->count(),
            'in_review_count' => Verification::where('institution_id', $institution->id)->where('status', 'in_review')->count(),
            'approved_count' => Verification::where('institution_id', $institution->id)->where('status', 'approved')->count(),
            'rejected_count' => Verification::where('institution_id', $institution->id)->where('status', 'rejected')->count(),
            'recent_verifications' => Verification::where('institution_id', $institution->id)
                ->with(['portfolio_item.user'])
                ->latest()
                ->limit(5)
                ->get()
                ->map(function ($verification) {
                    return [
                        'id' => $verification->id,
                        'verification_number' => $verification->verification_number,
                        'status' => $verification->status,
                        'created_at' => $verification->created_at,
                        'portfolio_item' => [
                            'id' => $verification->portfolio_item->id,
                            'title' => $verification->portfolio_item->title,
                            'type' => $verification->portfolio_item->type,
                            'user' => [
                                'id' => $verification->portfolio_item->portfolio->user->id,
                                'name' => $verification->portfolio_item->portfolio->user->name,
                                'email' => $verification->portfolio_item->portfolio->user->email
                            ]
                        ]
                    ];
                })
        ];

        return response()->json($stats);
    }
}