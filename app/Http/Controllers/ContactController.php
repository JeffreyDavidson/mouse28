<?php

namespace App\Http\Controllers;

use App\Mail\ContactFormSubmitted;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function show()
    {
        return view('contact');
    }

    public function store(Request $request)
    {
        // Honeypot check
        if ($request->filled('website') || $request->filled('fax_number')) {
            return back()->with('success', true);
        }

        // Rate limit: 3 per minute per IP
        $key = 'contact-form:' . $request->ip();
        if (cache()->get($key, 0) >= 3) {
            return back()->with('success', true);
        }
        cache()->increment($key);
        cache()->put($key, cache()->get($key), 60);

        // Gibberish detection
        $text = $request->input('message') . ' ' . $request->input('name');
        if (preg_match('/[^\x20-\x7E\n\r\t]/', $text) && mb_strlen($text) > 50) {
            $asciiRatio = strlen(preg_replace('/[^\x20-\x7E]/', '', $text)) / max(mb_strlen($text), 1);
            if ($asciiRatio < 0.5) {
                return back()->with('success', true);
            }
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        $contactMessage = ContactMessage::create($validated);

        try {
            Mail::to(config('mail.admin_address', 'hello@mouse28.com'))
                ->send(new ContactFormSubmitted($contactMessage));
        } catch (\Throwable $e) {
            Log::error('Failed to send contact notification: ' . $e->getMessage());
        }

        return back()->with('success', true);
    }
}
