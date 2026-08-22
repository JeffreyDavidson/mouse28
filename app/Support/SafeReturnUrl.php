<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SafeReturnUrl
{
    public static function from(Request $request, string $fallback): string
    {
        $candidate = $request->headers->get('referer');

        if (! is_string($candidate)) {
            return $fallback;
        }

        $candidateParts = parse_url($candidate);
        $applicationParts = parse_url(url('/'));

        if (! is_array($candidateParts) || ! is_array($applicationParts)) {
            return $fallback;
        }

        if (($candidateParts['scheme'] ?? null) !== ($applicationParts['scheme'] ?? null)
            || ($candidateParts['host'] ?? null) !== ($applicationParts['host'] ?? null)
            || self::port($candidateParts) !== self::port($applicationParts)) {
            return $fallback;
        }

        return Str::before($candidate, '#');
    }

    /** @param array<string, mixed> $parts */
    private static function port(array $parts): ?int
    {
        if (isset($parts['port']) && is_int($parts['port'])) {
            return $parts['port'];
        }

        return match ($parts['scheme'] ?? null) {
            'http' => 80,
            'https' => 443,
            default => null,
        };
    }
}
