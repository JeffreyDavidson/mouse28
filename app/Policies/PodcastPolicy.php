<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Podcast;
use App\Models\User;

class PodcastPolicy
{
    public function update(User $user, Podcast $podcast): bool
    {
        return $user->is_admin === true;
    }
}
