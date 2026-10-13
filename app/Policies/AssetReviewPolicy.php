<?php

namespace App\Policies;

use App\Models\AssetReview;
use App\Models\User;

class AssetReviewPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_admin;
    }

    public function view(User $user, AssetReview $review): bool
    {
        return $user->is_admin;
    }

    public function create(User $user): bool
    {
        return $user->is_admin;
    }

    public function update(User $user, AssetReview $review): bool
    {
        return $user->is_admin;
    }
}
