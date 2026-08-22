<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_admin === true;
    }

    public function view(User $user, Post $post): bool
    {
        return $user->is_admin === true;
    }

    public function create(User $user): bool
    {
        return $user->is_admin === true;
    }

    public function update(User $user, Post $post): bool
    {
        return $user->is_admin === true;
    }

    public function delete(User $user, Post $post): bool
    {
        return $user->is_admin === true;
    }

    public function deleteAny(User $user): bool
    {
        return $user->is_admin === true;
    }

    public function restore(User $user, Post $post): bool
    {
        return $user->is_admin === true;
    }

    public function restoreAny(User $user): bool
    {
        return $user->is_admin === true;
    }

    public function forceDelete(User $user, Post $post): bool
    {
        return $user->is_admin === true;
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->is_admin === true;
    }
}
