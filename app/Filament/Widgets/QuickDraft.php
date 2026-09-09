<?php

namespace App\Filament\Widgets;

use App\Enums\ContentAuthor;
use App\Models\Post;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Widgets\Widget;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/** @property-read Schema $form */
class QuickDraft extends Widget implements HasForms
{
    use InteractsWithForms;

    #[\Override]
    protected static ?int $sort = 5;

    #[\Override]
    protected int|string|array $columnSpan = 1;

    #[\Override]
    protected string $view = 'filament.widgets.quick-draft';

    /** @var array<string, mixed>|null */
    public ?array $data = [];

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

        Post::query()->create([
            'title' => $state['title'],
            'slug' => $this->uniqueSlug(Arr::string($state, 'title')),
            'excerpt' => $state['notes'] ?? null,
            'body' => $state['notes'] ?? '',
            'author' => ContentAuthor::Both,
            'is_published' => false,
        ]);

        $this->data = [];
        $this->form->fill();

        Notification::make()
            ->title('Draft saved!')
            ->success()
            ->send();
    }

    private function uniqueSlug(string $title): string
    {
        $baseSlug = Str::substr(Str::slug($title) ?: 'draft', 0, 255);
        $slug = $baseSlug;
        $suffix = 2;

        while (Post::withTrashed()->where('slug', $slug)->exists()) {
            $slug = Str::substr($baseSlug, 0, 255 - strlen("-{$suffix}"))."-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
