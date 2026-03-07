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
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
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

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([
                EmbeddedSchema::make('form'),
            ]),
        ]);
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('General')
                    ->icon('heroicon-o-microphone')
                    ->description('Your podcast name, description, and cover art')
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
                    ->icon('heroicon-o-signal')
                    ->description('Where listeners can find your podcast')
                    ->columns(2)
                    ->schema([
                        TextInput::make('apple_url')->url()->label('Apple Podcasts')
                            ->prefixIcon('heroicon-o-link'),
                        TextInput::make('spotify_url')->url()->label('Spotify')
                            ->prefixIcon('heroicon-o-link'),
                        TextInput::make('youtube_url')->url()->label('YouTube')
                            ->prefixIcon('heroicon-o-link'),
                        TextInput::make('rss_url')->url()->label('RSS Feed')
                            ->prefixIcon('heroicon-o-rss'),
                    ]),

                Section::make('Social Media')
                    ->icon('heroicon-o-heart')
                    ->description('Connect your social accounts')
                    ->columns(2)
                    ->schema([
                        TextInput::make('instagram_url')->url()->label('Instagram')
                            ->prefixIcon('heroicon-o-link'),
                        TextInput::make('tiktok_url')->url()->label('TikTok')
                            ->prefixIcon('heroicon-o-link'),
                    ]),

                Actions::make([
                    Action::make('save')
                        ->label('Save Settings')
                        ->icon('heroicon-o-check')
                        ->action(fn () => $this->save()),
                ])->alignEnd(),
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
}
