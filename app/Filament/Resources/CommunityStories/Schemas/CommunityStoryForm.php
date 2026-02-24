<?php

namespace App\Filament\Resources\CommunityStories\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CommunityStoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Submission Details')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')->required()->maxLength(255),
                        TextInput::make('email')->email()->required()->maxLength(255),
                        TextInput::make('location')->maxLength(255),
                        TextInput::make('child_name')->label('Child\'s Name')->maxLength(255),
                        TextInput::make('child_age')->label('Child\'s Age')->numeric(),
                        TextInput::make('favorite_park')->maxLength(255),
                    ]),

                Section::make('Story')
                    ->schema([
                        TextInput::make('title')->required()->maxLength(255),
                        Textarea::make('story')->required()->rows(8)->columnSpanFull(),
                        Textarea::make('favorite_tip')->label('Their #1 Tip')->rows(3)->columnSpanFull(),
                    ]),

                Section::make('Moderation')
                    ->columns(3)
                    ->schema([
                        Toggle::make('photo_consent')->label('Photo Consent'),
                        Toggle::make('is_approved')->label('Approved'),
                        Toggle::make('is_featured')->label('Featured'),
                    ]),
            ]);
    }
}
