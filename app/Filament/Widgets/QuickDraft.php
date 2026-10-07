<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\PublishStatus;
use App\Models\Post;
use App\Models\User;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Widgets\Widget;

/** @property-read Schema $form */
class QuickDraft extends Widget implements HasForms
{
    use InteractsWithForms;

    #[\Override]
    protected static ?int $sort = 4;

    #[\Override]
    protected int|string|array $columnSpan = 1;

    #[\Override]
    protected string $view = 'filament.widgets.quick-draft';

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Post title...'),
                Textarea::make('notes')
                    ->rows(3)
                    ->maxLength(300)
                    ->placeholder('Quick notes or ideas...'),
            ])
            ->statePath('data');
    }

    public function saveDraft(): void
    {
        $state = $this->form->getState();

        $post = Post::query()->create([
            'title' => $state['title'],
            'excerpt' => $state['notes'] ?? null,
            'content' => $state['notes'] ?? '',
            'status' => PublishStatus::Draft,
        ]);
        $post->syncAuthors(User::authorIds());

        $this->data = [];
        $this->form->fill();

        Notification::make()
            ->title('Draft saved!')
            ->success()
            ->send();
    }
}
