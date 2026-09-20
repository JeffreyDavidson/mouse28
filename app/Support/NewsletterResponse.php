<?php

namespace App\Support;

use App\Http\Requests\StoreNewsletterRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class NewsletterResponse
{
    public function success(StoreNewsletterRequest $request): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect($request->redirectUrl())->with('newsletter_success', true);
    }

    public function error(StoreNewsletterRequest $request, int $status): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Something went wrong.'], $status);
        }

        return redirect($request->redirectUrl())
            ->withInput($request->only('email'))
            ->with('newsletter_error', 'Something went wrong. Please try again.');
    }
}
