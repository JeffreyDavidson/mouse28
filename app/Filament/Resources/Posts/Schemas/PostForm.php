<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Filament\Forms\Components\AuthorsSelect;
use App\Filament\Forms\Components\ContentTagsInput;
use App\Filament\Forms\Components\OptimizedImageUpload;
use App\Filament\Forms\Components\PublishDatePicker;
use App\Filament\Forms\Components\PublishStatusSelect;
use App\Filament\Forms\Components\SeoSection;
use App\Filament\Forms\Components\SlugInput;
use App\Filament\Forms\Components\SlugSourceInput;
use App\Models\Category;
use App\Models\Post;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Config;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Post Details')
                    ->icon(Heroicon::OutlinedInformationCircle)
                    ->description('Basic post information')
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
                        Select::make('category_id')
                            ->label('Category')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionAction(fn (Action $action): Action => $action->authorize('create', Category::class))
                            ->createOptionForm([
                                TextInput::make('name')->required()
                                    ->maxLength(255),
                                SlugInput::make('slug'),
                            ])
                            ->required()
                            ->extraAlpineAttributes(['data-mouse28-accessible-select' => true])
                            ->columnSpan(1),
                        AuthorsSelect::make('authors')
                            ->columnSpan(1),
                        Select::make('episodes')
                            ->label('Related Episodes')
                            ->relationship('episodes', 'title')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->extraAlpineAttributes(['data-mouse28-accessible-select' => true])
                            ->columnSpan(2),
                    ]),

                Section::make('Content')
                    ->icon(Heroicon::OutlinedDocumentText)
                    ->description('Post excerpt and body content')
                    ->schema([
                        Textarea::make('excerpt')
                            ->rows(3)
                            ->maxLength(300)
                            ->helperText('Short summary shown in post listings.'),
                        MarkdownEditor::make('content')
                            ->dehydrateStateUsing(fn (?string $state): string => $state ?? ''),
                    ]),

                Section::make('Review')
                    ->schema([
                        Textarea::make('review_notes')
                            ->label('Review Notes')
                            ->rows(3)
                            ->helperText('Feedback from the reviewer')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(fn (?Post $record): bool => $record?->review_notes === null),

                Section::make('Review & Source')
                    ->description('Optional for evergreen posts. Policy and planning posts should include both fields.')
                    ->collapsed()
                    ->columns(2)
                    ->schema([
                        TextInput::make('source_url')
                            ->label('Official Source')
                            ->url()
                            ->maxLength(255)
                            ->required(fn (Get $get): bool => filled($get('last_reviewed_at')))
                            ->helperText('Link to the official policy or primary source.'),
                        DatePicker::make('last_reviewed_at')
                            ->label('Last Reviewed')
                            ->required(fn (Get $get): bool => filled($get('source_url')))
                            ->helperText('Sourced posts are flagged after '.Config::integer('content.post_review_interval_days').' days.'),
                    ]),

                Grid::make(2)
                    ->schema([
                        Section::make('Media')
                            ->icon(Heroicon::OutlinedPhoto)
                            ->schema([
                                OptimizedImageUpload::make('featured_image_path')
                                    ->directory('posts'),
                            ]),

                        Section::make('Publishing')
                            ->icon(Heroicon::OutlinedRocketLaunch)
                            ->description('Save the post, then use the Publish action when its content is ready.')
                            ->schema([
                                PublishStatusSelect::make('status'),
                                PublishDatePicker::make('published_at')
                                    ->label('Publish Date')
                                    ->helperText('Optional. Leave blank to publish immediately, or choose a future date to schedule.'),
                            ]),
                    ]),

                SeoSection::make(),
            ]);
    }
}
