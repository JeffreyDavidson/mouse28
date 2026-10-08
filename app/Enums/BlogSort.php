<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * The blog archive's order, stored in the `sort` query string parameter.
 */
enum BlogSort: string
{
    case Newest = 'newest';
    case Oldest = 'oldest';

    /** The given value's sort, or newest first when the value is unknown. */
    public static function fromInput(string $value): self
    {
        return self::tryFrom($value) ?? self::Newest;
    }

    public function label(): string
    {
        return match ($this) {
            self::Newest => 'Newest first',
            self::Oldest => 'Oldest first',
        };
    }

    /** @return 'asc'|'desc' */
    public function direction(): string
    {
        return match ($this) {
            self::Newest => 'desc',
            self::Oldest => 'asc',
        };
    }
}
