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
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        $contactMessage = ContactMessage::create($validated);

        try {
            $recipients = array_filter(array_map('trim', explode(',', config('mail.admin_address', 'hello@mouse28.com'))));
            Mail::to($recipients)
                ->send(new ContactFormSubmitted($contactMessage));
        } catch (\Throwable $e) {
            Log::error('Failed to send contact notification: ' . $e->getMessage());
        }

        return back()->with('success', true);
    }
}
