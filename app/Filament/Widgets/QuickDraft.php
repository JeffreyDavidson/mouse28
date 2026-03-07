<?php

namespace App\Filament\Widgets;

use App\Models\Post;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use Filament\Widgets\Widget;
use Illuminate\Support\Str;

class QuickDraft extends Widget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

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
                Actions::make([
                    Action::make('saveDraft')
                        ->label('Save Draft')
                        ->icon('heroicon-o-plus')
                        ->action(fn () => $this->saveDraft()),
                ])->alignEnd(),
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
