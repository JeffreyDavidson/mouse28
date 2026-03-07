<?php

namespace App\Filament\Widgets;

use App\Models\Post;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Widgets\Widget;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Support\Str;

class QuickDraft extends Widget implements HasForms
{
    use InteractsWithForms;

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 1;

    protected string $view = 'filament.widgets.quick-draft';

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
                    ->placeholder('Quick notes or ideas...'),
            ])
            ->statePath('data');
    }

    public function saveDraft(): void
    {
        $state = $this->form->getState();

        Post::create([
            'title' => $state['title'],
            'slug' => Str::slug($state['title']),
            'excerpt' => $state['notes'] ?? null,
            'author' => 'both',
            'is_published' => false,
        ]);

        $this->data = [];
        $this->form->fill();

        Notification::make()
            ->title('Draft saved!')
            ->success()
            ->send();
    }
}
