<?php

namespace App\Filament\Resources\Guides\Schemas;

use App\Models\Guide;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class GuideForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Guide Details')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                                if (! $get('slug') || $get('slug') === Str::slug($get('title'))) {
                                    $set('slug', Str::slug($state));
                                }
                            }),
                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Select::make('park')
                            ->options(Guide::PARKS)
                            ->required(),
                        Select::make('category')
                            ->options(Guide::CATEGORIES)
                            ->required(),
                        TextInput::make('icon')
                            ->label('Icon (emoji)')
                            ->maxLength(10),
                        TextInput::make('sort_order')
                            ->numeric()
                            ->default(0),
                    ]),

                Section::make('Content')
                    ->schema([
                        Textarea::make('excerpt')
                            ->rows(3)
                            ->maxLength(500),
                        RichEditor::make('body')
                            ->required()
                            ->columnSpanFull(),
                    ]),

                Section::make('Publishing')
                    ->schema([
                        Toggle::make('is_published')
                            ->label('Published')
                            ->default(true),
                    ]),
            ]);
    }
}
