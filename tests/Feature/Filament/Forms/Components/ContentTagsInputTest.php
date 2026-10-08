<?php

use App\Filament\Forms\Components\ContentTagsInput;
use App\Filament\Resources\Episodes\Pages\CreateEpisode;
use App\Filament\Resources\Guides\Pages\CreateGuide;
use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

covers(ContentTagsInput::class);

pest()->use(RefreshDatabase::class);

beforeEach(fn () => actingAs(User::factory()
    ->admin()
    ->create()));

test('tag fields use the content tag type', function (string $page): void {
    livewire($page)
        ->assertFormFieldExists('tags', fn (ContentTagsInput $field): bool => $field->getType() === 'content');
})->with([
    'post' => CreatePost::class,
    'guide' => CreateGuide::class,
    'episode' => CreateEpisode::class,
]);
