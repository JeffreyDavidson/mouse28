<?php

declare(strict_types=1);

namespace App\Enums;

use App\Filament\Resources\Episodes\EpisodeResource;
use App\Filament\Resources\Guides\GuideResource;
use App\Filament\Resources\Posts\PostResource;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum ContentType: string implements HasColor, HasIcon, HasLabel
{
    case Post = 'post';
    case Episode = 'episode';
    case Guide = 'guide';

    public function getLabel(): string
    {
        return match ($this) {
            self::Post => 'Post',
            self::Episode => 'Episode',
            self::Guide => 'Guide',
        };
    }

    public function pluralLabel(): string
    {
        return match ($this) {
            self::Post => 'Blog Posts',
            self::Episode => 'Episodes',
            self::Guide => 'Guides',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Post => 'primary',
            self::Episode => 'warning',
            self::Guide => 'info',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Post => Heroicon::OutlinedDocumentText,
            self::Episode => Heroicon::OutlinedMicrophone,
            self::Guide => Heroicon::OutlinedBookOpen,
        };
    }

    /**
     * The dashboard's brand text colour, kept as literal classes so Tailwind finds them.
     */
    public function textClass(): string
    {
        return match ($this) {
            self::Post => 'text-mouse-purple',
            self::Episode => 'text-mouse-gold-dark',
            self::Guide => 'text-mouse-teal',
        };
    }

    /**
     * @return class-string<PostResource|EpisodeResource|GuideResource>
     */
    public function resource(): string
    {
        return match ($this) {
            self::Post => PostResource::class,
            self::Episode => EpisodeResource::class,
            self::Guide => GuideResource::class,
        };
    }
}
