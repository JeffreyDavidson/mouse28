<?php

namespace App\Filament\Resources\Posts;

use App\Enums\ContentType;
use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Filament\Resources\Posts\Schemas\PostForm;
use App\Filament\Resources\Posts\Tables\PostsTable;
use App\Models\Post;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PostResource extends Resource
{
    #[\Override]
    protected static ?string $model = Post::class;

    #[\Override]
    protected static ?string $recordTitleAttribute = 'title';

    #[\Override]
    protected static string|\UnitEnum|null $navigationGroup = 'Content';

    #[\Override]
    protected static ?int $navigationSort = 2;

    #[\Override]
    public static function getNavigationIcon(): BackedEnum|Htmlable|string|null
    {
        return ContentType::Post->getIcon();
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'slug', 'category.name', 'authors.name'];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function form(Schema $schema): Schema
    {
        return PostForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PostsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPosts::route('/'),
            'create' => CreatePost::route('/create'),
            'edit' => EditPost::route('/{record}/edit'),
        ];
    }
}
