<?php

namespace App\Http\Controllers;

use App\Support\Turnstile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class NewsletterController extends Controller
{
    public function store(Request $request, Turnstile $turnstile)
    {
        if ($request->filled('website_url')) {
            return $this->successResponse($request);
        }

        if (! $turnstile->passes($request, config('services.turnstile.newsletter_action'))) {
            throw ValidationException::withMessages([
                'cf-turnstile-response' => 'Please verify that you are human and try again.',
            ]);
        }

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $audienceId = config('services.resend.audience_id');
        if (! is_string($audienceId) || trim($audienceId) === '') {
            Log::error('Newsletter signup is missing its Resend audience configuration.');

            return $this->errorResponse($request, 503);
        }

        try {
            $response = Http::withToken(config('services.resend.key'))
                ->timeout(10)
                ->post("https://api.resend.com/audiences/{$audienceId}/contacts", [
                    'email' => $validated['email'],
                ]);

            if ($response->successful()) {
                return $this->successResponse($request);
            }

            Log::warning('Resend newsletter signup failed', [
                'status' => $response->status(),
            ]);

            return $this->errorResponse($request, 422);
        } catch (\Throwable $exception) {
            Log::error('Newsletter signup request failed', [
                'exception' => $exception::class,
            ]);

            return $this->errorResponse($request, 500);
        }
    }

    private function successResponse(Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect(url()->previous().'#newsletter')->with('newsletter_success', true);
    }

    private function errorResponse(Request $request, int $status)
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Something went wrong.'], $status);
        }

        return redirect(url()->previous().'#newsletter')
            ->withInput($request->only('email'))
            ->with('newsletter_error', 'Something went wrong. Please try again.');
    }
}
