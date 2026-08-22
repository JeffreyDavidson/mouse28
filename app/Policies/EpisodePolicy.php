<?php

namespace App\Policies;

use App\Models\Episode;
use App\Models\User;

class EpisodePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_admin === true;
    }

    public function view(User $user, Episode $episode): bool
    {
        return $user->is_admin === true;
    }

    public function create(User $user): bool
    {
        return $user->is_admin === true;
    }

    public function update(User $user, Episode $episode): bool
    {
        return $user->is_admin === true;
    }

    public function delete(User $user, Episode $episode): bool
    {
        return $user->is_admin === true;
    }

    public function deleteAny(User $user): bool
    {
        return $user->is_admin === true;
    }

    public function restore(User $user, Episode $episode): bool
    {
        return $user->is_admin === true;
    }

    public function restoreAny(User $user): bool
    {
        return $user->is_admin === true;
    }

    public function forceDelete(User $user, Episode $episode): bool
    {
        return $user->is_admin === true;
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->is_admin === true;
    }
}
