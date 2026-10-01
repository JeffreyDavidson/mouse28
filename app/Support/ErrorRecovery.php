<?php

declare(strict_types=1);

namespace App\Support;

use Filament\Facades\Filament;
use Illuminate\Http\Request;

class ErrorRecovery
{
    /** @return array{url: string, label: string} */
    public static function for(Request $request, int $status): array
    {
        if ($request->is('contact')) {
            return ['url' => route('contact.create'), 'label' => 'Return to contact'];
        }

        // The archive lives at /newsletter too, so only the sign-up form's own requests return to it.
        if ($request->is('newsletter') && ! $request->isMethodSafe()) {
            return ['url' => route('home').'#newsletter', 'label' => 'Return to newsletter'];
        }

        if ($request->is('admin', 'admin/*')) {
            return ['url' => Filament::getPanel('admin')->getLoginUrl() ?? route('home'), 'label' => 'Return to admin login'];
        }

        if ($status !== 419 && $request->isMethodSafe()) {
            return ['url' => url()->current(), 'label' => 'Try again'];
        }

        return ['url' => route('home'), 'label' => 'Return to the site'];
    }
}
