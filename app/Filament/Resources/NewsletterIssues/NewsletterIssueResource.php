<?php

namespace App\Filament\Resources\NewsletterIssues;

use App\Enums\NavigationGroup;
use App\Filament\Resources\NewsletterIssues\Pages\CreateNewsletterIssue;
use App\Filament\Resources\NewsletterIssues\Pages\EditNewsletterIssue;
use App\Filament\Resources\NewsletterIssues\Pages\ListNewsletterIssues;
use App\Filament\Resources\NewsletterIssues\Schemas\NewsletterIssueForm;
use App\Filament\Resources\NewsletterIssues\Tables\NewsletterIssuesTable;
use App\Models\NewsletterIssue;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class NewsletterIssueResource extends Resource
{
    #[\Override]
    protected static ?string $model = NewsletterIssue::class;

    #[\Override]
    protected static ?string $recordTitleAttribute = 'title';

    #[\Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNewspaper;

    #[\Override]
    protected static string|\UnitEnum|null $navigationGroup = NavigationGroup::Content;

    #[\Override]
    protected static ?int $navigationSort = 3;

    #[\Override]
    protected static ?string $navigationLabel = 'Newsletter Issues';

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'slug'];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function form(Schema $schema): Schema
    {
        return NewsletterIssueForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NewsletterIssuesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNewsletterIssues::route('/'),
            'create' => CreateNewsletterIssue::route('/create'),
            'edit' => EditNewsletterIssue::route('/{record}/edit'),
        ];
    }
}
