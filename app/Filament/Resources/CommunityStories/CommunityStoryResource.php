<?php

namespace App\Filament\Resources\CommunityStories;

use App\Filament\Resources\CommunityStories\Pages\CreateCommunityStory;
use App\Filament\Resources\CommunityStories\Pages\EditCommunityStory;
use App\Filament\Resources\CommunityStories\Pages\ListCommunityStories;
use App\Filament\Resources\CommunityStories\Schemas\CommunityStoryForm;
use App\Filament\Resources\CommunityStories\Tables\CommunityStoriesTable;
use App\Models\CommunityStory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CommunityStoryResource extends Resource
{
    protected static ?string $model = CommunityStory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHeart;

    protected static string|\UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return CommunityStoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CommunityStoriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCommunityStories::route('/'),
            'create' => CreateCommunityStory::route('/create'),
            'edit' => EditCommunityStory::route('/{record}/edit'),
        ];
    }
}
