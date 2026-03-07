<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Models\Episode;
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

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Post Details')
                    ->icon('heroicon-o-information-circle')
                    ->description('Basic post information')
                    ->columns(4)
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                                if (! $get('slug') || $get('slug') === Str::slug($get('title'))) {
                                    $set('slug', Str::slug($state));
                                }
                            }),
                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2)
                            ->unique(ignoreRecord: true),
                        Select::make('category')
                            ->options(\App\Models\Post::CATEGORIES)
                            ->required()
                            ->columnSpan(1),
                        Select::make('author')
                            ->options(\App\Models\Post::AUTHORS)
                            ->required()
                            ->default('both')
                            ->columnSpan(1),
                        Select::make('episode_id')
                            ->label('Related Episode')
                            ->relationship('episode', 'title')
                            ->searchable()
                            ->preload()
                            ->placeholder('None')
                            ->columnSpan(2),
                    ]),

                Section::make('Content')
                    ->icon('heroicon-o-document-text')
                    ->description('Post excerpt and body content')
                    ->schema([
                        Textarea::make('excerpt')
                            ->rows(3)
                            ->maxLength(300)
                            ->helperText('Short summary shown in post listings.'),
                        MarkdownEditor::make('body'),
                    ]),

                Grid::make(2)
                    ->schema([
                        Section::make('Media')
                            ->icon('heroicon-o-photo')
                            ->schema([
                                FileUpload::make('cover_image')
                                    ->image()
                                    ->disk('public')
                                    ->directory('posts'),
                            ]),

                        Section::make('Publishing')
                            ->icon('heroicon-o-rocket-launch')
                            ->schema([
                                Toggle::make('is_published')
                                    ->label('Published')
                                    ->default(false),
                                DateTimePicker::make('published_at')
                                    ->label('Publish Date'),
                            ]),
                    ]),

                Section::make('SEO')
                    ->icon('heroicon-o-magnifying-glass')
                    ->description('Search engine optimization')
                    ->collapsed()
                    ->columns(2)
                    ->schema([
                        TextInput::make('meta_title')
                            ->maxLength(70)
                            ->helperText('50–70 characters recommended.'),
                        Textarea::make('meta_description')
                            ->maxLength(160)
                            ->rows(2)
                            ->helperText('120–160 characters recommended.'),
                        FileUpload::make('og_image')
                            ->image()
                            ->disk('public')
                            ->directory('posts/og')
                            ->helperText('Custom social sharing image. Falls back to cover.'),
                    ]),
            ]);
    }
}
