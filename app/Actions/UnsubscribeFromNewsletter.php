<?php

namespace App\Actions;

use App\Models\Subscriber;
use Illuminate\Support\Facades\Date;

final class UnsubscribeFromNewsletter
{
    public function handle(Subscriber $subscriber): void
    {
        $subscriber->fill([
            'unsubscribed_at' => Date::now(),
        ]);
        $subscriber->verification_token_hash = null;
        $subscriber->save();
    }
}
