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
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

class EpisodeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Episode Details')
                    ->icon(Heroicon::OutlinedInformationCircle)
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
                            ->unique(),
                        TextInput::make('episode_number')
                            ->integer()
                            ->minValue(0)
                            ->maxValue(2147483647)
                            ->unique()
                            ->required()
                            ->columnSpan(1),
                        TextInput::make('season_number')
                            ->integer()
                            ->minValue(0)
                            ->maxValue(4294967295)
                            ->default(1)
                            ->columnSpan(1),
                    ]),

                Grid::make(2)
                    ->schema([
                        Section::make('Media')
                            ->icon(Heroicon::OutlinedPhoto)
                            ->schema([
                                TextInput::make('transistor_url')
                                    ->label('Transistor Episode URL')
                                    ->url()
                                    ->maxLength(255)
                                    ->rules(['regex:/\Ahttps:\/\/share\.transistor\.fm\/s\/[a-zA-Z0-9]+\/?\z/'])
                                    ->prefixIcon(Heroicon::OutlinedLink)
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
                                    ->integer()
                                    ->minValue(0)
                                    ->maxValue(2147483647)
                                    ->suffix('seconds')
                                    ->prefixIcon(Heroicon::OutlinedClock),
                            ]),

                        Section::make('Publishing')
                            ->icon(Heroicon::OutlinedRocketLaunch)
                            ->description('Save the episode, then use the Publish action when its editorial content is ready. Transcripts may be added later.')
                            ->schema([
                                DateTimePicker::make('published_at')
                                    ->label('Publish Date')
                                    ->helperText('Optional. Leave blank to publish immediately, or choose a future date to schedule.'),
                            ]),
                    ]),

                Section::make('Content')
                    ->icon(Heroicon::OutlinedDocumentText)
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
                            ->icon(Heroicon::OutlinedSignal)
                            ->description('Where listeners can find this episode')
                            ->schema([
                                TextInput::make('apple_url')->url()->maxLength(255)->label('Apple Podcasts')->prefixIcon(Heroicon::OutlinedLink),
                                TextInput::make('spotify_url')->url()->maxLength(255)->label('Spotify')->prefixIcon(Heroicon::OutlinedLink),
                                TextInput::make('youtube_url')->url()->maxLength(255)->label('YouTube')->prefixIcon(Heroicon::OutlinedLink),
                            ]),

                        Section::make('SEO')
                            ->icon(Heroicon::OutlinedMagnifyingGlass)
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
