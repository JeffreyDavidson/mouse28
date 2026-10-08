<?php

namespace App\Filament\Resources\NewsletterIssues\Schemas;

use App\Filament\Forms\Components\PublishDatePicker;
use App\Filament\Forms\Components\PublishStatusSelect;
use App\Filament\Forms\Components\SlugInput;
use App\Filament\Forms\Components\SlugSourceInput;
use App\Models\NewsletterIssue;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as Router;
use Illuminate\Support\Str;

class NewsletterIssueForm
{
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
                        SlugSourceInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),
                        SlugInput::make('slug')
                            ->lockedAfterPublication()
                            ->notIn(fn (): array => self::reservedSlugs())
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
                        PublishStatusSelect::make('status'),
                        PublishDatePicker::make('published_at')
                            ->disabled(fn (?NewsletterIssue $record): bool => $record?->wasSent() ?? false)
                            ->helperText(fn (?NewsletterIssue $record): string => $record?->wasSent() === true
                                ? 'Locked after sending, so emailed links keep working.'
                                : 'Optional. Leave blank to publish immediately, or choose a future date to schedule.'),
                    ]),
            ]);
    }

    /**
     * Slugs taken by fixed /newsletter/* pages, such as the feed and the confirmed page,
     * which are registered before the issue route and would make an issue with the same
     * slug unreachable.
     *
     * @return array<int, string>
     */
    private static function reservedSlugs(): array
    {
        return collect(Router::getRoutes()->getRoutes())
            ->map(fn (Route $route): string => $route->uri())
            ->filter(fn (string $uri): bool => preg_match('#\Anewsletter/[^/{]+\z#', $uri) === 1)
            ->map(fn (string $uri): string => Str::after($uri, 'newsletter/'))
            ->unique()
            ->values()
            ->all();
    }
}
