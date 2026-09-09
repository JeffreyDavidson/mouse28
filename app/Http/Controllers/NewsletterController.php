<?php

namespace App\Http\Controllers;

use App\Enums\NewsletterSubscriptionResult;
use App\Http\Requests\StoreNewsletterRequest;
use App\Support\ResendAudience;
use App\Support\SafeReturnUrl;
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
    ): JsonResponse|RedirectResponse {
        if ($request->filled('website_url')) {
            return $this->successResponse($request);
        }

        if (! $turnstile->passes($request, Config::string('services.turnstile.newsletter_action'))) {
            throw ValidationException::withMessages([
                'cf-turnstile-response' => 'Please verify that you are human and try again.',
            ])->errorBag('newsletter')->redirectTo($this->redirectUrl($request));
        }

        $result = $audience->subscribe($request->safe()->string('email')->toString());

        if ($result === NewsletterSubscriptionResult::Subscribed) {
            return $this->successResponse($request);
        }

        return $this->errorResponse($request, $result->statusCode());
    }

    private function successResponse(StoreNewsletterRequest $request): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect($this->redirectUrl($request))->with('newsletter_success', true);
    }

    private function errorResponse(StoreNewsletterRequest $request, int $status): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Something went wrong.'], $status);
        }

        return redirect($this->redirectUrl($request))
            ->withInput($request->only('email'))
            ->with('newsletter_error', 'Something went wrong. Please try again.');
    }

    private function redirectUrl(StoreNewsletterRequest $request): string
    {
        return SafeReturnUrl::from($request, route('home')).'#newsletter';
    }
}
