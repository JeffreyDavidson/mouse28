<?php

namespace App\Filament\Resources\Guides\Schemas;

use App\Models\Guide;
use App\Models\Post;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class GuideForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Guide Details')
                    ->columns(4)
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set, ?string $state): void {
                                if (! $get('slug')) {
                                    $set('slug', Str::slug($state));
                                }
                            }),
                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->columnSpan(2),
                        Select::make('category')
                            ->options(Guide::CATEGORIES)
                            ->required()
                            ->columnSpan(2),
                        Select::make('author')
                            ->options(Post::AUTHORS)
                            ->required()
                            ->default('both')
                            ->columnSpan(2),
                    ]),
                Section::make('Content')
                    ->schema([
                        Textarea::make('excerpt')
                            ->maxLength(300)
                            ->rows(3),
                        MarkdownEditor::make('body')
                            ->required(),
                    ]),
                Grid::make(2)
                    ->schema([
                        Section::make('Review & Sources')
                            ->schema([
                                TextInput::make('source_url')
                                    ->url()
                                    ->maxLength(255)
                                    ->helperText('Link to the official policy or primary source.'),
                                DatePicker::make('last_reviewed_at')
                                    ->label('Last Reviewed')
                                    ->helperText('Guides are flagged after '.config('mouse28.guide_review_interval_days').' days.'),
                            ]),
                        Section::make('Publishing')
                            ->schema([
                                Toggle::make('is_published')->default(false),
                                DateTimePicker::make('published_at'),
                            ]),
                    ]),
                Section::make('Media & SEO')
                    ->collapsed()
                    ->columns(2)
                    ->schema([
                        FileUpload::make('cover_image')->image()->disk('public')->directory('guides'),
                        FileUpload::make('og_image')->image()->disk('public')->directory('guides/og'),
                        TextInput::make('meta_title')->maxLength(70),
                        Textarea::make('meta_description')->maxLength(160)->rows(2),
                    ]),
            ]);
    }
}
