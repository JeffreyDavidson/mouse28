<?php

namespace App\Enums;

enum NewsletterSubscriptionResult
{
    case Subscribed;
    case ConfigurationMissing;
    case ProviderRejected;
    case ConnectionFailed;

    public function statusCode(): int
    {
        return match ($this) {
            self::Subscribed => 200,
            self::ConfigurationMissing => 503,
            self::ProviderRejected => 422,
            self::ConnectionFailed => 500,
        };
    }
}
