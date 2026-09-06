<?php

use App\Filament\Resources\Posts\Pages\EditPost;
use App\Filament\Resources\Posts\PostResource;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

test('edit page offers a draft preview', function (): void {
    $admin = User::factory()->admin()->create();
    $post = Post::factory()->draft()->create();

    actingAs($admin);

    get(PostResource::getUrl('edit', ['record' => $post]))
        ->assertOk()
        ->assertSee('Preview');
});

test('ready drafts can be explicitly published and unpublished', function (): void {
    $admin = User::factory()->admin()->create();
    $record = Post::factory()->draft()->create([
        'cover_image' => 'posts/complete.jpg',
        'meta_title' => 'Complete post title',
        'meta_description' => 'Complete post description',
    ]);

    actingAs($admin);

    Livewire::test(EditPost::class, ['record' => $record->getRouteKey()])
        ->callAction('publish')
        ->assertNotified();

    expect($record->refresh()->is_published)->toBeTrue()
        ->and($record->published_at)->not->toBeNull();

    Livewire::test(EditPost::class, ['record' => $record->getRouteKey()])
        ->callAction('unpublish')
        ->assertNotified();

    expect($record->refresh()->is_published)->toBeFalse();
});

test('deleted content leaves the public site and can be restored by an administrator', function (): void {
    $admin = User::factory()->admin()->create();
    $record = Post::factory()->create();

    $record->delete();

    get(route('blog.show', $record))->assertNotFound();

    actingAs($admin);

    Livewire::test(EditPost::class, ['record' => $record->getRouteKey()])
        ->callAction('restore')
        ->assertNotified();

    expect($record->refresh()->deleted_at)->toBeNull();
    get(route('blog.show', $record))->assertOk();
});

test('publishing is blocked until editorial requirements are complete', function (): void {
    $admin = User::factory()->admin()->create();
    $post = Post::factory()->draft()->create([
        'excerpt' => null,
        'cover_image' => null,
        'meta_title' => null,
        'meta_description' => null,
    ]);

    actingAs($admin);

    Livewire::test(EditPost::class, ['record' => $post->getRouteKey()])
        ->callAction('publish')
        ->assertNotified();

    expect($post->refresh()->is_published)->toBeFalse();
});
