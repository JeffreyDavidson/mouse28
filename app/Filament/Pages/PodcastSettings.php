<?php

namespace App\Filament\Pages;

use App\Models\Podcast;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class PodcastSettings extends Page
{
    protected string $view = 'filament.pages.podcast-settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Podcast Settings';

    protected static ?string $title = 'Podcast Settings';

    public ?array $data = [];

    public function mount(): void
    {
        $podcast = Podcast::info();
        $this->form->fill($podcast->toArray());
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('General')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')->required()->maxLength(255),
                        TextInput::make('email')->email()->maxLength(255),
                        Textarea::make('description')->rows(3)->columnSpanFull(),
                        FileUpload::make('cover_image')
                            ->image()
                            ->disk('public')
                            ->directory('podcast'),
                    ]),

                Section::make('Distribution Links')
                    ->columns(2)
                    ->schema([
                        TextInput::make('apple_url')->url()->label('Apple Podcasts'),
                        TextInput::make('spotify_url')->url()->label('Spotify'),
                        TextInput::make('youtube_url')->url()->label('YouTube'),
                        TextInput::make('rss_url')->url()->label('RSS Feed'),
                    ]),

                Section::make('Social Media')
                    ->columns(2)
                    ->schema([
                        TextInput::make('instagram_url')->url()->label('Instagram'),
                        TextInput::make('tiktok_url')->url()->label('TikTok'),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $podcast = Podcast::info();
        $podcast->update($data);

        Notification::make()
            ->title('Settings saved')
            ->success()
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Settings')
                ->submit('save'),
        ];
    }
}
