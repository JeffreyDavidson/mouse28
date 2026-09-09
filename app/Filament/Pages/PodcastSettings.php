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

/** @property-read Schema $form */
class PodcastSettings extends Page
{
    #[\Override]
    protected string $view = 'filament.pages.podcast-settings';

    #[\Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    #[\Override]
    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    #[\Override]
    protected static ?string $navigationLabel = 'Podcast Settings';

    #[\Override]
    protected static ?string $title = 'Podcast Settings';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->is_admin === true;
    }

    public function mount(): void
    {
        $podcast = Podcast::settings();
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
                    ->icon(Heroicon::OutlinedMicrophone)
                    ->description('Your podcast name, description, and cover art')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')->required()->maxLength(255),
                        TextInput::make('email')->email()->maxLength(255),
                        Textarea::make('description')->rows(3)->columnSpanFull(),
                        FileUpload::make('cover_image')
                            ->image()
                            ->maxSize(5120)
                            ->disk('public')
                            ->directory('podcast'),
                    ]),

                Section::make('Distribution Links')
                    ->icon(Heroicon::OutlinedSignal)
                    ->description('Where listeners can find your podcast')
                    ->columns(2)
                    ->schema([
                        TextInput::make('apple_url')->url()->maxLength(255)->label('Apple Podcasts')
                            ->prefixIcon(Heroicon::OutlinedLink),
                        TextInput::make('spotify_url')->url()->maxLength(255)->label('Spotify')
                            ->prefixIcon(Heroicon::OutlinedLink),
                        TextInput::make('youtube_url')->url()->maxLength(255)->label('YouTube')
                            ->prefixIcon(Heroicon::OutlinedLink),
                    ]),

                Section::make('Social Media')
                    ->icon(Heroicon::OutlinedHeart)
                    ->description('Connect your social accounts')
                    ->columns(2)
                    ->schema([
                        TextInput::make('instagram_url')->url()->maxLength(255)->label('Instagram')
                            ->prefixIcon(Heroicon::OutlinedLink),
                        TextInput::make('tiktok_url')->url()->maxLength(255)->label('TikTok')
                            ->prefixIcon(Heroicon::OutlinedLink),
                    ]),

                Actions::make([
                    Action::make('save')
                        ->label('Save Settings')
                        ->icon(Heroicon::OutlinedCheck)
                        ->action(fn () => $this->save()),
                ])->alignEnd(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $podcast = Podcast::settings();
        $podcast->update($data);

        Notification::make()
            ->title('Settings saved')
            ->success()
            ->send();
    }
}
