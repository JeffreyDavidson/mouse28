<?php

namespace App\Http\Controllers;

use App\Actions\RequestNewsletterSubscription;
use App\Http\Requests\SubscribeNewsletterRequest;
use Illuminate\Http\RedirectResponse;

class NewsletterSubscriptionController
{
    public function store(SubscribeNewsletterRequest $request, RequestNewsletterSubscription $requestNewsletterSubscription): RedirectResponse
    {
        $requestNewsletterSubscription->handle($request->safe()
            ->string('email')
            ->toString());

        return redirect($request->redirectUrl())->with('newsletter_success', SubscribeNewsletterRequest::CHECK_YOUR_EMAIL);
    }
}
