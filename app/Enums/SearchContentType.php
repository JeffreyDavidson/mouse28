<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * The site search's result groups, in the order the search page shows them. Each value
 * keys its group and names its heading anchor (`search-{value}`).
 */
enum SearchContentType: string
{
    case Posts = 'posts';
    case Guides = 'guides';
    case Episodes = 'episodes';

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $type): array => [$type->value => $type->label()])
            ->all();
    }

    public function label(): string
    {
        return match ($this) {
            self::Posts => 'Blog posts',
            self::Guides => 'Guides',
            self::Episodes => 'Podcast episodes',
        };
    }
}
