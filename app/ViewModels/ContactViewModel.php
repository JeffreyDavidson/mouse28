<?php

namespace App\ViewModels;

use App\Models\Podcast;
use Illuminate\Support\Facades\Config;

class ContactViewModel
{
    /** @return array{contactEmail: string, contactFormAvailable: bool} */
    public function data(): array
    {
        return [
            'contactEmail' => Podcast::info()->email ?: Config::string('mail.admin_address'),
            'contactFormAvailable' => filled(config('services.turnstile.site_key'))
                && filled(config('services.turnstile.secret_key')),
        ];
    }
}
