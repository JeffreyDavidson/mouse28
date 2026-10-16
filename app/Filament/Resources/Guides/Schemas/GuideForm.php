<?php

declare(strict_types=1);

namespace App\Filament\Resources\Guides\Schemas;

use App\Enums\GuideCategory;
use App\Filament\Forms\Components\AuthorsSelect;
use App\Filament\Forms\Components\ContentTagsInput;
use App\Filament\Forms\Components\OptimizedImageUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Config;
use JeffreyDavidson\CreatorKit\Filament\Forms\Components\PublishDatePicker;
use JeffreyDavidson\CreatorKit\Filament\Forms\Components\PublishStatusSelect;
use JeffreyDavidson\CreatorKit\Filament\Forms\Components\SeoSection;
use JeffreyDavidson\CreatorKit\Filament\Forms\Components\SlugInput;
use JeffreyDavidson\CreatorKit\Filament\Forms\Components\SlugSourceInput;
use JeffreyDavidson\CreatorKit\Filament\Forms\Components\SourceReviewSection;

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
                        SourceReviewSection::make()
                            ->heading('Review & Sources')
                            ->icon(Heroicon::OutlinedClipboardDocumentCheck)
                            ->requiredTogether(false)
                            ->reviewIntervalDays(fn (): int => Config::integer('content.guide_review_interval_days')),
                        Section::make('Publishing')
                            ->icon(Heroicon::OutlinedRocketLaunch)
                            ->description('Save the guide, then use the Publish action when its content is ready.')
                            ->schema([
                                PublishStatusSelect::make('status'),
                                PublishDatePicker::make('published_at'),
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
