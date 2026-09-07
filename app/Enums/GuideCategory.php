<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum GuideCategory: string implements HasLabel
{
    case Accessibility = 'accessibility';
    case ParkStrategy = 'park-strategy';
    case FoodReviews = 'food-reviews';
    case FamilyPlanning = 'family-planning';

    public function getLabel(): string
    {
        return match ($this) {
            self::Accessibility => 'Accessibility',
            self::ParkStrategy => 'Park Strategy',
            self::FoodReviews => 'Food & Reviews',
            self::FamilyPlanning => 'Family Planning',
        };
    }
}
