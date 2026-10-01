<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsletterIssues\Tables;

use App\Enums\PublicationStatus;
use App\Models\NewsletterIssue;
use App\Support\Content\PreviewUrlGenerator;
use App\Support\EditorialReadiness;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class NewsletterIssuesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(60),
                TextColumn::make('status')
                    ->badge()
                    ->getStateUsing(fn (NewsletterIssue $record): PublicationStatus => EditorialReadiness::status($record)),
                TextColumn::make('published_at')
                    ->label('Published')
                    ->date()
                    ->placeholder('Not published')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('view_on_site')
                    ->label('View on site')
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->url(fn (NewsletterIssue $record, PreviewUrlGenerator $previewUrls): string => $record->isPublished()
                        ? route('newsletter.issue', $record)
                        : $previewUrls->for($record))
                    ->openUrlInNewTab(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->emptyStateIcon(Heroicon::OutlinedNewspaper)
            ->emptyStateHeading('No newsletter issues yet')
            ->emptyStateDescription('Draft the first issue when there is an update worth sending.');
    }
}
