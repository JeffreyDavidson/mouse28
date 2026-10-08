<?php

declare(strict_types=1);

namespace App\Filament\Resources\Episodes\Schemas;

use App\Filament\Forms\Components\ContentTagsInput;
use App\Filament\Forms\Components\OptimizedImageUpload;
use App\Filament\Forms\Components\PublishDatePicker;
use App\Filament\Forms\Components\PublishStatusSelect;
use App\Filament\Forms\Components\SeoSection;
use App\Filament\Forms\Components\SlugInput;
use App\Filament\Forms\Components\SlugSourceInput;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class EpisodeForm
{
    /** Largest value of a signed 32-bit integer column. */
    private const int MAX_SIGNED_INT = 2147483647;

    /** Largest value of an unsigned 32-bit integer column. */
    private const int MAX_UNSIGNED_INT = 4294967295;

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
                        ContentTagsInput::make('tags')
                            ->columnSpanFull(),
                        SlugSourceInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),
                        SlugInput::make('slug')
                            ->lockedAfterPublication()
                            ->columnSpan(2),
                        TextInput::make('episode_number')
                            ->integer()
                            ->minValue(0)
                            ->maxValue(self::MAX_SIGNED_INT)
                            ->unique()
                            ->required()
                            ->columnSpan(1),
                        TextInput::make('season_number')
                            ->integer()
                            ->minValue(0)
                            ->maxValue(self::MAX_UNSIGNED_INT)
                            ->default(1)
                            ->columnSpan(1),
                    ]),

                Section::make('Guest')
                    ->icon(Heroicon::OutlinedUser)
                    ->description('Optional. Shown in the admin only for now.')
                    ->columns(3)
                    ->collapsed()
                    ->schema([
                        TextInput::make('guest_name')
                            ->label('Name')
                            ->maxLength(255),
                        TextInput::make('guest_title')
                            ->label('Title')
                            ->maxLength(255),
                        TextInput::make('guest_url')
                            ->label('Link')
                            ->url()
                            ->maxLength(255),
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
                                OptimizedImageUpload::make('featured_image_path')
                                    ->directory('episodes'),
                                TextInput::make('duration_seconds')
                                    ->integer()
                                    ->minValue(0)
                                    ->maxValue(self::MAX_SIGNED_INT)
                                    ->suffix('seconds')
                                    ->prefixIcon(Heroicon::OutlinedClock),
                            ]),

                        Section::make('Publishing')
                            ->icon(Heroicon::OutlinedRocketLaunch)
                            ->description('Save the episode, then use the Publish action when its editorial content is ready. Transcripts may be added later.')
                            ->schema([
                                PublishStatusSelect::make('status')
                                    ->withoutReview(),
                                PublishDatePicker::make('published_at')
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
                            ->description('Apple Podcasts and Spotify links are set once for the show in Podcast Settings.')
                            ->schema([
                                TextInput::make('youtube_url')
                                    ->url()
                                    ->maxLength(255)
                                    ->label('YouTube')
                                    ->prefixIcon(Heroicon::OutlinedLink),
                            ]),

                        SeoSection::make(),
                    ]),
            ]);
    }
}
