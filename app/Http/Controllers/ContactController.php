<?php

namespace App\Http\Controllers;

use App\Mail\ContactFormConfirmation;
use App\Mail\ContactFormSubmitted;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class ContactController extends Controller
{
    public function show()
    {
        return view('contact');
    }

    public function store(Request $request)
    {
        // Honeypot: if this hidden field is filled, it is a bot
        if ($request->filled('website_url')) {
            return back()->with('success', true);
        }

        $this->validateTurnstile($request);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        $contactMessage = ContactMessage::create($validated);

        try {
            $recipients = array_filter(array_map('trim', explode(',', config('mail.admin_address', 'mouse28podcast@gmail.com'))));
            Mail::to($recipients)
                ->send(new ContactFormSubmitted($contactMessage));
        } catch (\Throwable $e) {
            Log::error('Failed to send contact notification: '.$e->getMessage());
        }

        try {
            Mail::to($contactMessage->email)
                ->send(new ContactFormConfirmation($contactMessage));
        } catch (\Throwable $e) {
            Log::error('Failed to send contact confirmation: '.$e->getMessage());
        }

        return back()->with('success', true);
    }

    private function validateTurnstile(Request $request): void
    {
        $token = $request->input('cf-turnstile-response');
        $secret = config('services.turnstile.secret_key');
        $endpoint = config('services.turnstile.siteverify_url', 'https://challenges.cloudflare.com/turnstile/v0/siteverify');

        if (! is_string($token) || trim($token) === '' || ! is_string($secret) || trim($secret) === '') {
            $this->throwTurnstileValidationException();
        }

        try {
            $response = Http::asForm()
                ->timeout(5)
                ->post($endpoint, [
                    'secret' => $secret,
                    'response' => $token,
                    'remoteip' => $request->ip(),
                ]);
        } catch (\Throwable $e) {
            Log::warning('Turnstile verification request failed: '.$e->getMessage());
            $this->throwTurnstileValidationException();
        }

        if (! $response->ok() || $response->json('success') !== true) {
            $this->throwTurnstileValidationException();
        }
    }

    private function throwTurnstileValidationException(): never
    {
        throw ValidationException::withMessages([
            'cf-turnstile-response' => 'Please verify that you are human and try again.',
        ]);
    }
}
