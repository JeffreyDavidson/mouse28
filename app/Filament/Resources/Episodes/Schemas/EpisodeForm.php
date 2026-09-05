<?php

namespace App\Filament\Resources\Episodes\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
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
            ->columns(1)
            ->components([
                Section::make('Episode Details')
                    ->icon('heroicon-o-information-circle')
                    ->description('Basic episode information')
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
                        TextInput::make('episode_number')
                            ->numeric()
                            ->required()
                            ->columnSpan(1),
                        TextInput::make('season_number')
                            ->numeric()
                            ->default(1)
                            ->columnSpan(1),
                    ]),

                Grid::make(2)
                    ->schema([
                        Section::make('Media')
                            ->icon('heroicon-o-photo')
                            ->schema([
                                TextInput::make('transistor_url')
                                    ->label('Transistor Episode URL')
                                    ->url()
                                    ->maxLength(500)
                                    ->rules(['regex:/\Ahttps:\/\/share\.transistor\.fm\/s\/[a-zA-Z0-9]+\/?\z/'])
                                    ->prefixIcon('heroicon-o-link')
                                    ->helperText('Paste the episode share URL, such as https://share.transistor.fm/s/428d650c. Mouse28 builds the embedded player from it.'),
                                FileUpload::make('cover_image')
                                    ->image()
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->maxSize(5120)
                                    ->imageAspectRatio('1200:630')
                                    ->automaticallyCropImagesToAspectRatio()
                                    ->automaticallyResizeImagesMode('cover')
                                    ->automaticallyResizeImagesToWidth('1600')
                                    ->automaticallyResizeImagesToHeight('840')
                                    ->disk('public')
                                    ->directory('episodes')
                                    ->helperText('Landscape image (1.91:1), up to 5 MB. Uploads are cropped and resized automatically.'),
                                TextInput::make('duration_seconds')
                                    ->numeric()
                                    ->suffix('seconds')
                                    ->prefixIcon('heroicon-o-clock'),
                            ]),

                        Section::make('Publishing')
                            ->icon('heroicon-o-rocket-launch')
                            ->description('Save the episode, then use the Publish action when its editorial content is ready. Transcripts may be added later.')
                            ->schema([
                                DateTimePicker::make('published_at')
                                    ->label('Publish Date')
                                    ->helperText('Optional. Leave blank to publish immediately, or choose a future date to schedule.'),
                            ]),
                    ]),

                Section::make('Content')
                    ->icon('heroicon-o-document-text')
                    ->description('Episode description, show notes, and transcript')
                    ->schema([
                        Textarea::make('description')
                            ->rows(3)
                            ->maxLength(500)
                            ->helperText('Short description shown in episode listings.'),
                        RichEditor::make('show_notes')
                            ->toolbarButtons([
                                'bold', 'italic', 'link',
                                'h2', 'h3',
                                'bulletList', 'orderedList',
                                'blockquote',
                                'undo', 'redo',
                            ])
                            ->helperText('Use H2 for main sections (e.g. "What We Cover"), H3 for subsections (e.g. "Timestamps"). Bold speaker names in timestamps.'),
                        RichEditor::make('transcript')
                            ->toolbarButtons([
                                'bold', 'italic',
                                'undo', 'redo',
                            ])
                            ->helperText('Format each line as: <strong>Speaker:</strong> dialogue text. Use italic for stage directions like (laughing).'),
                    ]),

                Grid::make(2)
                    ->schema([
                        Section::make('Distribution')
                            ->icon('heroicon-o-signal')
                            ->description('Where listeners can find this episode')
                            ->schema([
                                TextInput::make('apple_url')->url()->label('Apple Podcasts')->prefixIcon('heroicon-o-link'),
                                TextInput::make('spotify_url')->url()->label('Spotify')->prefixIcon('heroicon-o-link'),
                                TextInput::make('youtube_url')->url()->label('YouTube')->prefixIcon('heroicon-o-link'),
                            ]),

                        Section::make('SEO')
                            ->icon('heroicon-o-magnifying-glass')
                            ->description('Search engine optimization')
                            ->collapsed()
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
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->maxSize(5120)
                                    ->imageAspectRatio('1200:630')
                                    ->automaticallyCropImagesToAspectRatio()
                                    ->automaticallyResizeImagesMode('cover')
                                    ->automaticallyResizeImagesToWidth('1200')
                                    ->automaticallyResizeImagesToHeight('630')
                                    ->disk('public')
                                    ->directory('episodes/og')
                                    ->helperText('Custom social sharing image. Falls back to cover.'),
                            ]),
                    ]),
            ]);
    }
}
