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
            ->components([
                Section::make('Post Details')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                                if (! $get('slug') || $get('slug') === Str::slug($get('title'))) {
                                    $set('slug', Str::slug($state));
                                }
                            }),
                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Select::make('category')
                            ->options(\App\Models\Post::CATEGORIES)
                            ->required(),
                        Select::make('author')
                            ->options(\App\Models\Post::AUTHORS)
                            ->required()
                            ->default('both'),
                    ]),

                Section::make('Content')
                    ->schema([
                        Textarea::make('excerpt')
                            ->rows(3)
                            ->maxLength(300),
                        MarkdownEditor::make('body')
                            ->columnSpanFull(),
                    ]),

                Section::make('Media')
                    ->schema([
                        FileUpload::make('cover_image')
                            ->image()
                            ->disk('public')
                            ->directory('posts'),
                    ]),

                Section::make('Relations')
                    ->schema([
                        Select::make('episode_id')
                            ->label('Related Episode')
                            ->relationship('episode', 'title')
                            ->searchable()
                            ->preload()
                            ->placeholder('None'),
                    ]),

                Section::make('Publishing')
                    ->columns(2)
                    ->schema([
                        Toggle::make('is_published')
                            ->label('Published')
                            ->default(false),
                        DateTimePicker::make('published_at')
                            ->label('Publish Date'),
                    ]),

                Section::make('SEO')
                    ->collapsed()
                    ->schema([
                        TextInput::make('meta_title')
                            ->maxLength(70)
                            ->helperText('Recommended: 50–70 characters for optimal display in search results.'),
                        Textarea::make('meta_description')
                            ->maxLength(160)
                            ->rows(2)
                            ->helperText('Recommended: 120–160 characters for search result snippets.'),
                        FileUpload::make('og_image')
                            ->image()
                            ->disk('public')
                            ->directory('posts/og')
                            ->helperText('Custom image for social media sharing. Falls back to cover image.'),
                    ]),
            ]);
    }
}
