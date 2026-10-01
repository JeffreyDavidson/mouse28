<?php

namespace App\Http\Controllers;

use App\Actions\RequestNewsletterSubscription;
use App\Http\Requests\SubscribeNewsletterRequest;
use App\Services\TurnstileVerifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\ValidationException;

class NewsletterSubscriptionController
{
    private const string CHECK_YOUR_EMAIL = 'Check your email to confirm your sign-up.';

    public function store(
        SubscribeNewsletterRequest $request,
        TurnstileVerifier $turnstile,
        RequestNewsletterSubscription $requestNewsletterSubscription,
    ): RedirectResponse {
        if ($request->filled('website_url')) {
            return $this->checkYourEmail($request);
        }

        if (! $turnstile->passes($request, Config::string('services.turnstile.newsletter_action'))) {
            throw ValidationException::withMessages([
                'cf-turnstile-response' => 'Please verify that you are human and try again.',
            ])->errorBag('newsletter')->redirectTo($request->redirectUrl());
        }

        $requestNewsletterSubscription->handle($request->safe()->string('email')->toString());

        return $this->checkYourEmail($request);
    }

    private function checkYourEmail(SubscribeNewsletterRequest $request): RedirectResponse
    {
        return redirect($request->redirectUrl())->with('newsletter_success', self::CHECK_YOUR_EMAIL);
    }
}
