<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\ContactType;

/**
 * A validated contact form submission. Mirrors The Laravel Architect's DTO
 * without its site-only budget and project fields.
 */
final readonly class ContactMessageData
{
    public function __construct(
        public string $name,
        public string $email,
        public ContactType $type,
        public string $message,
    ) {}
}
