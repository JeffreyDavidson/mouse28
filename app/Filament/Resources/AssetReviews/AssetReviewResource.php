<?php

declare(strict_types=1);

namespace App\Filament\Resources\AssetReviews;

use App\Enums\NavigationGroup;
use App\Filament\Resources\AssetReviews\Pages\CreateAssetReview;
use App\Filament\Resources\AssetReviews\Pages\EditAssetReview;
use App\Filament\Resources\AssetReviews\Pages\ListAssetReviews;
use App\Filament\Resources\AssetReviews\Schemas\AssetReviewForm;
use App\Filament\Resources\AssetReviews\Tables\AssetReviewsTable;
use App\Models\AssetReview;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class AssetReviewResource extends Resource
{
    #[\Override]
    protected static ?string $model = AssetReview::class;

    #[\Override]
    protected static ?string $recordTitleAttribute = 'title';

    #[\Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    #[\Override]
    protected static string|\UnitEnum|null $navigationGroup = NavigationGroup::Content;

    #[\Override]
    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return AssetReviewForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AssetReviewsTable::configure($table);
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAssetReviews::route('/'),
            'create' => CreateAssetReview::route('/create'),
            'edit' => EditAssetReview::route('/{record}/edit'),
        ];
    }
}
