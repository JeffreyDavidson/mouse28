<?php

declare(strict_types=1);

namespace App\Filament\Resources\SocialProfiles;

use App\Enums\NavigationGroup;
use App\Filament\Resources\SocialProfiles\Pages\CreateSocialProfile;
use App\Filament\Resources\SocialProfiles\Pages\EditSocialProfile;
use App\Filament\Resources\SocialProfiles\Pages\ListSocialProfiles;
use App\Filament\Resources\SocialProfiles\Schemas\SocialProfileForm;
use App\Filament\Resources\SocialProfiles\Tables\SocialProfilesTable;
use App\Models\SocialProfile;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SocialProfileResource extends Resource
{
    #[\Override]
    protected static ?string $model = SocialProfile::class;

    #[\Override]
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    #[\Override]
    protected static string|\UnitEnum|null $navigationGroup = NavigationGroup::Settings;

    #[\Override]
    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return SocialProfileForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SocialProfilesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSocialProfiles::route('/'),
            'create' => CreateSocialProfile::route('/create'),
            'edit' => EditSocialProfile::route('/{record}/edit'),
        ];
    }
}
