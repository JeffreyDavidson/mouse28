<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NewsletterController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        try {
            $response = Http::withToken(config('services.resend.key'))
                ->post('https://api.resend.com/audiences/05c28c48-d37a-4429-9fdf-6ee261c023f4/contacts', [
                    'email' => $request->email,
                ]);

            if ($response->successful()) {
                if ($request->expectsJson()) {
                    return response()->json(['success' => true]);
                }
                return redirect(url()->previous() . '#newsletter')->with('newsletter_success', true);
            }

            Log::warning('Resend newsletter signup failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            if ($request->expectsJson()) {
                return response()->json(['error' => 'Something went wrong.'], 422);
            }
            return redirect(url()->previous() . '#newsletter')->with('newsletter_error', 'Something went wrong. Please try again.');
        } catch (\Exception $e) {
            Log::error('Newsletter signup error', ['message' => $e->getMessage()]);

            if ($request->expectsJson()) {
                return response()->json(['error' => 'Something went wrong.'], 500);
            }
            return redirect(url()->previous() . '#newsletter')->with('newsletter_error', 'Something went wrong. Please try again.');
        }
    }
}
