<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Skill;
use App\Models\Badge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployerController extends Controller
{
    public function searchCandidates(Request $request)
    {
        $this->authorize('search', User::class);

        $validator = $request->validate([
            'skills' => 'nullable|array',
            'skills.*' => 'string',
            'badge_level' => 'nullable|in:beginner,intermediate,advanced,expert',
            'region' => 'nullable|string',
            'city' => 'nullable|string',
            'institution' => 'nullable|string',
            'min_experience' => 'nullable|integer|min:0',
            'page' => 'nullable|integer',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);

        $query = User::where('role', 'user')
            ->where('status', 'active')
            ->with(['portfolio', 'badges' => function($q) {
                $q->where('status', 'active');
            }]);

        // Filter by verified skills
        if ($request->has('skills')) {
            $query->whereHas('skills', function($q) use ($request) {
                $q->whereIn('skills.name', $request->skills)
                  ->where('user_skills.verification_status', 'verified');
            });
        }

        // Filter by badge level
        if ($request->has('badge_level')) {
            $query->whereHas('badges', function($q) use ($request) {
                $q->where('level', $request->badge_level)
                  ->where('status', 'active');
            });
        }

        // Location filters
        if ($request->has('region')) {
            $query->where('region', $request->region);
        }

        if ($request->has('city')) {
            $query->where('city', $request->city);
        }

        // Experience filter (years)
        if ($request->has('min_experience')) {
            $query->whereHas('skills', function($q) use ($request) {
                $q->having('user_skills.years_experience', '>=', $request->min_experience);
            });
        }

        // Sort by relevance (number of verified skills)
        $query->withCount(['skills' => function($q) {
            $q->where('user_skills.verification_status', 'verified');
        }])->orderBy('skills_count', 'desc');

        $perPage = $request->get('per_page', 20);
        $candidates = $query->paginate($perPage);

        // Mask sensitive data
        $candidates->through(function($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'region' => $user->region,
                'city' => $user->city,
                'profile_photo' => $user->portfolio->profile_photo ?? null,
                'badges_count' => $user->badges->count(),
                'verified_skills_count' => $user->skills_count,
                'badges' => $user->badges->map(function($badge) {
                    return [
                        'name' => $badge->name,
                        'skill_name' => $badge->skill_name,
                        'level' => $badge->level,
                        'issuer' => $badge->issuer->institution_name ?? $badge->issuer->name,
                        'issued_at' => $badge->issued_at,
                    ];
                }),
            ];
        });

        return response()->json($candidates);
    }

    public function viewCandidateProfile($id)
    {
        $this->authorize('view', User::class);

        $user = User::where('role', 'user')
            ->where('status', 'active')
            ->with([
                'portfolio' => function($q) {
                    $q->with(['items' => function($query) {
                        $query->where('status', 'verified')
                              ->latest();
                    }]);
                },
                'badges' => function($q) {
                    $q->where('status', 'active')
                      ->with('issuer');
                },
                'skills' => function($q) {
                    $q->where('user_skills.verification_status', 'verified')
                      ->withPivot('proficiency', 'years_experience');
                }
            ])
            ->findOrFail($id);

        // Prepare public profile
        $profile = [
            'name' => $user->name,
            'region' => $user->region,
            'city' => $user->city,
            'bio' => $user->portfolio->bio ?? null,
            'profile_photo' => $user->portfolio->profile_photo ?? null,
            'verified_skills' => $user->skills->map(function($skill) {
                return [
                    'name' => $skill->name,
                    'proficiency' => $skill->pivot->proficiency,
                    'years_experience' => $skill->pivot->years_experience,
                ];
            }),
            'badges' => $user->badges->map(function($badge) {
                return [
                    'name' => $badge->name,
                    'skill_name' => $badge->skill_name,
                    'level' => $badge->level,
                    'issuer' => $badge->issuer->institution_name ?? $badge->issuer->name,
                    'issued_at' => $badge->issued_at->format('Y-m-d'),
                    'verification_url' => url('/api/verify/badge/' . $badge->badge_id),
                ];
            }),
            'portfolio_items' => $user->portfolio->items->map(function($item) {
                return [
                    'type' => $item->type,
                    'title' => $item->title,
                    'description' => $item->description,
                    'organization' => $item->organization,
                    'issue_date' => $item->issue_date?->format('Y-m-d'),
                ];
            }),
        ];

        // Log profile view
        if (auth()->check()) {
            \App\Models\AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'view_candidate_profile',
                'entity_type' => 'user',
                'entity_id' => $user->id,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);
        }

        return response()->json($profile);
    }

    public function verifyCredential(Request $request)
    {
        $validator = $request->validate([
            'badge_id' => 'required_without:verification_number|string',
            'verification_number' => 'required_without:badge_id|string',
        ]);

        if ($request->has('badge_id')) {
            $badge = Badge::where('badge_id', $request->badge_id)
                ->with(['user', 'issuer', 'verification'])
                ->firstOrFail();

            return response()->json([
                'valid' => $badge->isValid(),
                'badge' => [
                    'name' => $badge->name,
                    'skill' => $badge->skill_name,
                    'level' => $badge->level,
                    'recipient' => $badge->user->name,
                    'issuer' => $badge->issuer->institution_name ?? $badge->issuer->name,
                    'issued_at' => $badge->issued_at->format('Y-m-d'),
                    'expires_at' => $badge->expires_at?->format('Y-m-d'),
                    'status' => $badge->status,
                ]
            ]);
        }

        if ($request->has('verification_number')) {
            $verification = Verification::where('verification_number', $request->verification_number)
                ->with(['portfolioItem.portfolio.user', 'institution'])
                ->firstOrFail();

            return response()->json([
                'valid' => $verification->status === 'approved',
                'credential' => [
                    'title' => $verification->portfolioItem->title,
                    'type' => $verification->portfolioItem->type,
                    'recipient' => $verification->portfolioItem->portfolio->user->name,
                    'issuer' => $verification->institution->institution_name ?? $verification->institution->name,
                    'verified_at' => $verification->verified_at?->format('Y-m-d'),
                    'status' => $verification->status,
                ]
            ]);
        }
    }
}