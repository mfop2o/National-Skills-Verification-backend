<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Verification;

class VerificationPolicy
{
    public function viewAny(User $user)
    {
        return $user->canVerify() || $user->isAdmin();
    }

    public function view(User $user, Verification $verification)
    {
        return $user->id === $verification->institution_id || 
               $user->isAdmin() ||
               $user->id === $verification->portfolioItem->portfolio->user_id;
    }

    public function update(User $user, Verification $verification)
    {
        return ($user->id === $verification->institution_id && $verification->status === 'pending') ||
               $user->isAdmin();
    }

    public function revoke(User $user, Verification $verification)
    {
        return $user->isAdmin() || 
               ($user->id === $verification->institution_id && $user->canVerify());
    }
}