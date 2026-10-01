<?php

namespace App\Actions;

use App\Models\Subscriber;
use Illuminate\Support\Facades\Date;

final class ConfirmNewsletterSubscription
{
    public function handle(Subscriber $subscriber): void
    {
        $subscriber->fill([
            'verified_at' => Date::now(),
            'unsubscribed_at' => null,
        ]);
        $subscriber->verification_token_hash = null;
        $subscriber->save();
    }
}
