<?php

declare(strict_types=1);

namespace App\Filament\Resources\Guides\Schemas;

use App\Enums\GuideCategory;
use App\Filament\Forms\Components\AuthorsSelect;
use App\Filament\Forms\Components\ContentTagsInput;
use App\Filament\Forms\Components\OptimizedImageUpload;
use App\Filament\Forms\Components\PublishDatePicker;
use App\Filament\Forms\Components\PublishStatusSelect;
use App\Filament\Forms\Components\SeoSection;
use App\Filament\Forms\Components\SlugInput;
use App\Filament\Forms\Components\SlugSourceInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Config;

class GuideForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Guide Details')
                    ->icon(Heroicon::OutlinedInformationCircle)
                    ->description('Basic guide information')
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
                        Select::make('category')
                            ->options(GuideCategory::class)
                            ->required()
                            ->columnSpan(2),
                        AuthorsSelect::make('authors')
                            ->columnSpan(2),
                    ]),
                Section::make('Content')
                    ->icon(Heroicon::OutlinedDocumentText)
                    ->description('Guide excerpt and body content')
                    ->schema([
                        Textarea::make('excerpt')
                            ->maxLength(300)
                            ->rows(3),
                        MarkdownEditor::make('content')
                            ->required(),
                    ]),
                Grid::make(2)
                    ->schema([
                        Section::make('Review & Sources')
                            ->icon(Heroicon::OutlinedClipboardDocumentCheck)
                            ->description('Keep source and review information current.')
                            ->schema([
                                TextInput::make('source_url')
                                    ->url()
                                    ->maxLength(255)
                                    ->helperText('Link to the official policy or primary source.'),
                                DatePicker::make('last_reviewed_at')
                                    ->label('Last Reviewed')
                                    ->helperText('Guides are flagged after '.Config::integer('content.guide_review_interval_days').' days.'),
                            ]),
                        Section::make('Publishing')
                            ->icon(Heroicon::OutlinedRocketLaunch)
                            ->description('Save the guide, then use the Publish action when its content is ready.')
                            ->schema([
                                PublishStatusSelect::make('status'),
                                PublishDatePicker::make('published_at')
                                    ->helperText('Optional. Leave blank to publish immediately, or choose a future date to schedule.'),
                            ]),
                    ]),
                Grid::make(2)
                    ->schema([
                        Section::make('Media')
                            ->icon(Heroicon::OutlinedPhoto)
                            ->schema([
                                OptimizedImageUpload::make('featured_image_path')
                                    ->directory('guides'),
                            ]),

                        SeoSection::make(),
                    ]),
            ]);
    }
}
