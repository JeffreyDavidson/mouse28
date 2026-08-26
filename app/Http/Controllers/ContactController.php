<?php

namespace App\Http\Controllers;

use App\Actions\SendContactEmails;
use App\Http\Requests\StoreContactRequest;
use App\Models\ContactMessage;
use App\Models\Podcast;
use App\Support\Turnstile;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function show(): View
    {
        return view('contact', [
            'contactEmail' => Podcast::info()->email ?: config('mail.admin_address', 'mouse28podcast@gmail.com'),
        ]);
    }

    public function store(StoreContactRequest $request, Turnstile $turnstile, SendContactEmails $sendContactEmails)
    {
        // Honeypot: if this hidden field is filled, it is a bot
        if ($request->filled('website_url')) {
            return redirect()->route('contact.show')->with('success', true);
        }

        if (! $turnstile->passes($request, config('services.turnstile.contact_action'))) {
            throw ValidationException::withMessages([
                'cf-turnstile-response' => 'Please verify that you are human and try again.',
            ])->errorBag('contact');
        }

        $validated = $request->validated();

        $contactMessage = ContactMessage::query()->create($validated);
        $sendContactEmails($contactMessage);

        return redirect()->route('contact.show')->with('success', true);
    }
}
