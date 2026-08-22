<?php

namespace App\Policies;

use App\Models\ContactMessage;
use App\Models\User;

class ContactMessagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_admin === true;
    }

    public function view(User $user, ContactMessage $contactMessage): bool
    {
        return $user->is_admin === true;
    }

    public function update(User $user, ContactMessage $contactMessage): bool
    {
        return $user->is_admin === true;
    }

    public function delete(User $user, ContactMessage $contactMessage): bool
    {
        return $user->is_admin === true;
    }

    public function deleteAny(User $user): bool
    {
        return $user->is_admin === true;
    }
}
