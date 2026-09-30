<?php

namespace App\Filament\Resources\NewsletterIssues\Schemas;

use App\Models\NewsletterIssue;
use App\Support\ContentPermalink;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

class NewsletterIssueForm
{
    /** Slugs that would collide with fixed newsletter routes. */
    private const array RESERVED_SLUGS = ['rss'];

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Issue Details')
                    ->icon(Heroicon::OutlinedInformationCircle)
                    ->description('Title, permalink and summary shown in the public archive')
                    ->columns(4)
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set, ?string $state): void {
                                if (! $get('slug')) {
                                    $set('slug', Str::slug($state ?? ''));
                                }
                            }),
                        TextInput::make('slug')
                            ->regex('/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/')
                            ->notIn(self::RESERVED_SLUGS)
                            ->disabled(fn (?NewsletterIssue $record): bool => $record instanceof NewsletterIssue && ContentPermalink::isLocked($record))
                            ->helperText('Lowercase words separated by hyphens. URLs stay locked after first publication, even when unpublished or rescheduled.')
                            ->required()
                            ->maxLength(255)
                            ->unique()
                            ->columnSpan(2),
                        Textarea::make('excerpt')
                            ->maxLength(300)
                            ->rows(3)
                            ->helperText('Short summary shown in the archive and the feed.')
                            ->columnSpanFull(),
                    ]),
                Section::make('Content')
                    ->icon(Heroicon::OutlinedDocumentText)
                    ->description('The issue body, written in Markdown')
                    ->schema([
                        MarkdownEditor::make('content')
                            ->required(),
                    ]),
                Section::make('Publishing')
                    ->icon(Heroicon::OutlinedRocketLaunch)
                    ->description('Save the issue, then use the Publish action when its content is ready.')
                    ->schema([
                        DateTimePicker::make('published_at')
                            ->helperText('Optional. Leave blank to publish immediately, or choose a future date to schedule.'),
                    ]),
            ]);
    }
}
