<?php

declare(strict_types=1);

namespace App\Enums;

enum NewsletterSubscriptionResult
{
    case Subscribed;
    case Disabled;
    case ConfigurationMissing;
    case ProviderRejected;
    case ConnectionFailed;

    public function statusCode(): int
    {
        return match ($this) {
            self::Subscribed => 200,
            self::Disabled => 503,
            self::ConfigurationMissing => 503,
            self::ProviderRejected => 422,
            self::ConnectionFailed => 500,
        };
    }
}
