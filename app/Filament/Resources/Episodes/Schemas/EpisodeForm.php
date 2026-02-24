<?php

namespace App\Filament\Resources\Episodes\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class EpisodeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Episode Details')
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
                        TextInput::make('episode_number')
                            ->numeric()
                            ->required(),
                        TextInput::make('season_number')
                            ->numeric()
                            ->default(1),
                    ]),

                Section::make('Content')
                    ->schema([
                        Textarea::make('description')
                            ->rows(3)
                            ->maxLength(500),
                        MarkdownEditor::make('show_notes')
                            ->columnSpanFull(),
                    ]),

                Section::make('Media')
                    ->columns(2)
                    ->schema([
                        TextInput::make('audio_url')
                            ->url()
                            ->maxLength(500),
                        FileUpload::make('cover_image')
                            ->image()
                            ->disk('public')
                            ->directory('episodes'),
                        TextInput::make('duration_seconds')
                            ->numeric()
                            ->suffix('seconds'),
                    ]),

                Section::make('Distribution')
                    ->columns(3)
                    ->schema([
                        TextInput::make('apple_url')->url()->label('Apple Podcasts'),
                        TextInput::make('spotify_url')->url()->label('Spotify'),
                        TextInput::make('youtube_url')->url()->label('YouTube'),
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
            ]);
    }
}
