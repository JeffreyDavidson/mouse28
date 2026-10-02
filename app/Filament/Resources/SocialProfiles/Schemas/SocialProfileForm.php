<?php

declare(strict_types=1);

namespace App\Filament\Resources\SocialProfiles\Schemas;

use App\Enums\SocialPlatform;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SocialProfileForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Profile')
                    ->columns(2)
                    ->schema([
                        Select::make('platform')
                            ->options(SocialPlatform::class)
                            ->required()
                            ->live(),
                        TextInput::make('label')
                            ->label('Display label')
                            ->helperText('Optional text shown on the contact page. The platform name is used when blank.')
                            ->requiredIf('platform', SocialPlatform::Other->value)
                            ->maxLength(255),
                        TextInput::make('url')
                            ->label('Profile URL')
                            ->url()
                            ->rules(['url:https'])
                            ->required()
                            ->maxLength(2048)
                            ->columnSpanFull(),
                    ]),
                Section::make('Visibility')
                    ->columns(2)
                    ->schema([
                        Toggle::make('is_enabled')
                            ->label('Enabled')
                            ->default(true),
                        Toggle::make('show_in_footer')
                            ->label('Show in site footer')
                            ->default(true),
                        Toggle::make('show_on_contact')
                            ->label('Show on contact page')
                            ->default(false),
                        TextInput::make('sort_order')
                            ->integer()
                            ->minValue(0)
                            ->maxValue(65535)
                            ->default(0)
                            ->required(),
                    ]),
            ]);
    }
}
