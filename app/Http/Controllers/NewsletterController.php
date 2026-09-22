<?php

namespace App\Http\Controllers;

use App\Enums\NewsletterSubscriptionResult;
use App\Http\Requests\StoreNewsletterRequest;
use App\Support\NewsletterResponse;
use App\Support\ResendAudience;
use App\Support\Turnstile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\ValidationException;

class NewsletterController
{
    public function store(
        StoreNewsletterRequest $request,
        Turnstile $turnstile,
        ResendAudience $audience,
        NewsletterResponse $response,
    ): JsonResponse|RedirectResponse {
        if ($request->filled('website_url')) {
            return $response->success($request);
        }

        if (! $turnstile->passes($request, Config::string('services.turnstile.newsletter_action'))) {
            throw ValidationException::withMessages([
                'cf-turnstile-response' => 'Please verify that you are human and try again.',
            ])->errorBag('newsletter')->redirectTo($request->redirectUrl());
        }

        $result = $audience->subscribe($request->safe()->string('email')->toString());

        if ($result === NewsletterSubscriptionResult::Subscribed) {
            return $response->success($request);
        }

        return $response->error($request, $result->statusCode());
    }
}
