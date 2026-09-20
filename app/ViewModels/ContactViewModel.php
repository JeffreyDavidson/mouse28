<?php

declare(strict_types=1);

namespace App\ViewModels;

use Illuminate\Support\Facades\Config;

class ContactViewModel
{
    /** @return array{contactEmail: string, contactFormAvailable: bool} */
    public function data(): array
    {
        return [
            'contactEmail' => Config::string('mouse28.contact.email'),
            'contactFormAvailable' => filled(config('services.turnstile.site_key'))
                && filled(config('services.turnstile.secret_key')),
        ];
    }
}
