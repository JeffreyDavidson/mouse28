<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Turnstile
{
    public function passes(Request $request, string $expectedAction): bool
    {
        $token = $request->input('cf-turnstile-response');
        $secret = config('services.turnstile.secret_key');
        $endpoint = config('services.turnstile.siteverify_url');

        if (! is_string($token) || trim($token) === '' || ! is_string($secret) || trim($secret) === '') {
            return false;
        }

        try {
            $response = Http::asForm()
                ->timeout(5)
                ->post($endpoint, [
                    'secret' => $secret,
                    'response' => $token,
                    'remoteip' => $request->ip(),
                ]);
        } catch (\Throwable $exception) {
            Log::warning('Turnstile verification request failed', [
                'exception' => $exception::class,
            ]);

            return false;
        }

        $hostname = strtolower((string) $response->json('hostname', ''));
        $allowedHostnames = array_map('strtolower', array_filter(
            config('services.turnstile.allowed_hostnames', []),
            'is_string',
        ));

        return $response->ok()
            && $response->json('success') === true
            && $response->json('action') === $expectedAction
            && in_array($hostname, $allowedHostnames, true);
    }
}
