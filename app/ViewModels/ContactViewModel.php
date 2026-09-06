<?php

namespace App\ViewModels;

use App\Models\Podcast;

class ContactViewModel
{
    /** @return array{contactEmail: string, contactFormAvailable: bool} */
    public function data(): array
    {
        return [
            'contactEmail' => Podcast::info()->email ?: (string) config('mail.admin_address'),
            'contactFormAvailable' => filled(config('services.turnstile.site_key'))
                && filled(config('services.turnstile.secret_key')),
        ];
    }
}
