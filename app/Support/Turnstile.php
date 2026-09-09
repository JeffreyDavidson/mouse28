<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Turnstile
{
    public function passes(Request $request, string $expectedAction): bool
    {
        $token = $request->input('cf-turnstile-response');
        $secret = config('services.turnstile.secret_key');

        if (! is_string($token) || trim($token) === '' || ! is_string($secret) || trim($secret) === '') {
            return false;
        }

        try {
            $response = Http::asForm()
                ->timeout(5)
                ->post(Config::string('services.turnstile.siteverify_url'), [
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

        $hostname = $response->json('hostname');

        if (! is_string($hostname) || $hostname === '') {
            return false;
        }

        $allowedHostnames = [];

        foreach (Config::array('services.turnstile.allowed_hostnames', []) as $allowedHostname) {
            if (is_string($allowedHostname)) {
                $allowedHostnames[] = strtolower($allowedHostname);
            }
        }

        return $response->ok()
            && $response->json('success') === true
            && $response->json('action') === $expectedAction
            && in_array(strtolower($hostname), $allowedHostnames, true);
    }
}
