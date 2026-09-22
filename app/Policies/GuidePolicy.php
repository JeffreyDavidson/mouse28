<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Guide;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class GuidePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_admin === true;
    }

    public function view(User $user, Guide $guide): bool
    {
        return $user->is_admin === true;
    }

    public function viewPublic(?User $user, Guide $guide): Response
    {
        return $guide->is_published && $guide->published_at?->isPast()
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    public function create(User $user): bool
    {
        return $user->is_admin === true;
    }

    public function update(User $user, Guide $guide): bool
    {
        return $user->is_admin === true;
    }

    public function delete(User $user, Guide $guide): bool
    {
        return $user->is_admin === true;
    }

    public function deleteAny(User $user): bool
    {
        return $user->is_admin === true;
    }

    public function restore(User $user, Guide $guide): bool
    {
        return $user->is_admin === true;
    }

    public function restoreAny(User $user): bool
    {
        return $user->is_admin === true;
    }

    public function forceDelete(User $user, Guide $guide): bool
    {
        return $user->is_admin === true;
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->is_admin === true;
    }
}
