<?php

use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Filament\Resources\Posts\PostResource;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

test('authenticated user can render the resource listing', function (): void {
    actingAs(User::factory()->admin()->create());

    get(PostResource::getUrl())
        ->assertOk()
        ->assertSee('Blog Posts');
});

test('content table shows readiness and missing publish dates', function (): void {
    $admin = User::factory()->admin()->create();
    Post::factory()->create(['published_at' => null]);

    actingAs($admin);

    get(PostResource::getUrl())
        ->assertOk()
        ->assertSee('Readiness')
        ->assertSee('Needs publish date');
});

test('header does not count scheduled posts as published', function (): void {
    Post::factory()->create();
    Post::factory()->scheduled()->create();
    Post::factory()->draft()->create();
    actingAs(User::factory()->admin()->create());

    $header = Livewire::test(ListPosts::class)->instance()->getHeader();

    expect($header?->getData())
        ->toMatchArray([
            'total' => 3,
            'published' => 1,
            'drafts' => 1,
        ]);
});
