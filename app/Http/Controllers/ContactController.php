<?php

namespace App\Http\Controllers;

use App\Mail\ContactFormConfirmation;
use App\Mail\ContactFormSubmitted;
use App\Models\ContactMessage;
use App\Support\Turnstile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class ContactController extends Controller
{
    public function show()
    {
        return view('contact');
    }

    public function store(Request $request, Turnstile $turnstile)
    {
        // Honeypot: if this hidden field is filled, it is a bot
        if ($request->filled('website_url')) {
            return back()->with('success', true);
        }

        if (! $turnstile->passes($request, config('services.turnstile.contact_action'))) {
            throw ValidationException::withMessages([
                'cf-turnstile-response' => 'Please verify that you are human and try again.',
            ]);
        }

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
}
