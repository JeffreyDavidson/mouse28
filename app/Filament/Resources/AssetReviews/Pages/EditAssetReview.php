<?php

namespace App\Filament\Resources\AssetReviews\Pages;

use App\Actions\ReviewAsset;
use App\Enums\AssetReviewStatus;
use App\Filament\Resources\AssetReviews\AssetReviewResource;
use App\Models\AssetReview;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;

/** @property AssetReview $record */
class EditAssetReview extends EditRecord
{
    #[\Override]
    protected static string $resource = AssetReviewResource::class;

    #[Locked]
    public int $reviewRevision = 0;

    protected function afterFill(): void
    {
        $this->reviewRevision = $this->record->revision;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('approve')
                ->label('Approve')
                ->icon(Heroicon::OutlinedCheckCircle)
                ->color('success')
                ->authorize('update')
                ->visible(fn (): bool => $this->record->status !== AssetReviewStatus::Approved)
                ->modalSubmitActionLabel('Approve')
                ->modalDescription('Approve the saved images and version shown above. Save any edits first. Approval does not publish the set.')
                ->schema([Textarea::make('review_notes')
                    ->label('Comments')
                    ->maxLength(Config::integer('asset-reviews.max_comment_length'))])
                ->action(fn (ReviewAsset $reviewAsset, array $data) => $this->recordDecision($reviewAsset, AssetReviewStatus::Approved, $data)),
            Action::make('requestChanges')
                ->label('Request changes')
                ->modalSubmitActionLabel('Send feedback')
                ->icon(Heroicon::OutlinedChatBubbleLeftEllipsis)
                ->color('warning')
                ->authorize('update')
                ->schema([Textarea::make('review_notes')
                    ->label('What needs changing?')
                    ->required()
                    ->maxLength(Config::integer('asset-reviews.max_comment_length'))])
                ->action(fn (ReviewAsset $reviewAsset, array $data) => $this->recordDecision($reviewAsset, AssetReviewStatus::ChangesRequested, $data)),
        ];
    }

    /** @param array<array-key, mixed> $data */
    protected function recordDecision(ReviewAsset $action, AssetReviewStatus $status, array $data): void
    {
        $user = auth()
            ->user();
        abort_unless($user instanceof User, 403);
        try {
            $state = $this->form->getRawState();
            if ($state instanceof Arrayable) {
                $state = $state->toArray();
            }
            $files = $state['files'] ?? [];
            if ($state['title'] !== $this->record->title || $state['version'] !== $this->record->version || ($state['source_post_id'] ?? null) != $this->record->source_post_id || ! is_array($files) || array_values($files) !== $this->record->files) {
                throw ValidationException::withMessages(['review' => 'Save your edits, then review the saved set before making a decision.']);
            }

            $notes = $data['review_notes'] ?? null;
            if ($notes !== null && ! is_string($notes)) {
                throw ValidationException::withMessages(['review_notes' => 'Comments must be text.']);
            }
            $this->record = $action->handle($this->record, $user, $this->reviewRevision, $status, $notes);
        } catch (ValidationException $exception) {
            Notification::make()
                ->title('Review not saved')
                ->body($exception->getMessage())
                ->danger()
                ->persistent()
                ->send();

            throw $exception;
        }
        $this->reviewRevision = $this->record->revision;

        Notification::make()
            ->title($status->getLabel())
            ->success()
            ->send();
    }

    /** @param array<string, mixed> $data */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(function () use ($data): AssetReview {
            $saved = AssetReview::query()
                ->lockForUpdate()
                ->findOrFail($this->record->id);
            if ($saved->revision !== $this->reviewRevision) {
                throw ValidationException::withMessages(['data.version' => 'This set changed. Reload before saving your edits.']);
            }

            $saved->update($data);
            $this->reviewRevision = $saved->revision;

            return $saved;
        });
    }
}
