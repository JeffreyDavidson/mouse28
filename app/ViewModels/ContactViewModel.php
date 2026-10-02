<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\SocialProfile;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Config;

class ContactViewModel
{
    /** @return array{contactEmail: string, contactFormAvailable: bool, socialProfiles: Collection<int, SocialProfile>} */
    public function data(): array
    {
        return [
            'contactEmail' => Config::string('mouse28.contact.email'),
            'contactFormAvailable' => filled(config('services.turnstile.site_key'))
                && filled(config('services.turnstile.secret_key')),
            'socialProfiles' => SocialProfile::query()->forContactPage()->get(),
        ];
    }
}
