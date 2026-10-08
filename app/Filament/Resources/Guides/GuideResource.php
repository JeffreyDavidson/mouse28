<?php

namespace App\Filament\Resources\Guides;

use App\Enums\ContentType;
use App\Filament\Resources\Guides\Pages\CreateGuide;
use App\Filament\Resources\Guides\Pages\EditGuide;
use App\Filament\Resources\Guides\Pages\ListGuides;
use App\Filament\Resources\Guides\Schemas\GuideForm;
use App\Filament\Resources\Guides\Tables\GuidesTable;
use App\Models\Guide;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class GuideResource extends Resource
{
    #[\Override]
    protected static ?string $model = Guide::class;

    #[\Override]
    protected static ?string $recordTitleAttribute = 'title';

    #[\Override]
    protected static string|\UnitEnum|null $navigationGroup = 'Content';

    #[\Override]
    protected static ?int $navigationSort = 1;

    #[\Override]
    public static function getNavigationIcon(): BackedEnum|Htmlable|string|null
    {
        return ContentType::Guide->getIcon();
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'slug', 'category', 'authors.name'];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function form(Schema $schema): Schema
    {
        return GuideForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GuidesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGuides::route('/'),
            'create' => CreateGuide::route('/create'),
            'edit' => EditGuide::route('/{record}/edit'),
        ];
    }
}
