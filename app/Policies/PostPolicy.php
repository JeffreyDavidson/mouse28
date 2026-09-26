<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\Response;

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

    public function viewPublic(?User $user, Post $post): Response
    {
        return $post->isLive()
            ? Response::allow()
            : Response::denyAsNotFound();
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
