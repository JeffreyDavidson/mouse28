<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PostCategory: string implements HasColor, HasLabel
{
    case DisneyTips = 'disney-tips';
    case ParkAccessibility = 'park-accessibility';
    case EpisodeRecap = 'episode-recap';
    case FamilyLife = 'family-life';
    case AutismAwareness = 'autism-awareness';
    case DisneyNews = 'disney-news';
    case FoodReviews = 'food-reviews';
    case ResortReviews = 'resort-reviews';
    case DisneyPlus = 'disney-plus';
    case Merchandise = 'merchandise';
    case General = 'general';

    public function getLabel(): string
    {
        return match ($this) {
            self::DisneyTips => 'Disney Tips',
            self::ParkAccessibility => 'Park Accessibility',
            self::EpisodeRecap => 'Episode Recap',
            self::FamilyLife => 'Family Life',
            self::AutismAwareness => 'Autism Awareness',
            self::DisneyNews => 'Disney News',
            self::FoodReviews => 'Food Reviews',
            self::ResortReviews => 'Resort Reviews',
            self::DisneyPlus => 'Disney+',
            self::Merchandise => 'Merchandise',
            self::General => 'General',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::DisneyTips => 'info',
            self::ParkAccessibility => 'success',
            self::EpisodeRecap => 'warning',
            self::FamilyLife => 'danger',
            self::AutismAwareness => 'primary',
            default => 'gray',
        };
    }
}
