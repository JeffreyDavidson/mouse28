<?php

use App\Enums\ContentAuthor;
use App\Filament\Widgets\QuickDraft;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

pest()->use(RefreshDatabase::class);

test('duplicate maximum length titles keep generated slugs within the column limit', function (): void {
    $title = str_repeat('a', 255);
    Post::factory()->create(['slug' => $title]);
    foreach (range(2, 9) as $suffix) {
        Post::factory()->create(['slug' => str_repeat('a', 253)."-{$suffix}"]);
    }
    actingAs(User::factory()->admin()->create());

    Livewire::test(QuickDraft::class)
        ->set('data.title', $title)
        ->call('saveDraft')
        ->assertNotified();

    expect(Post::query()->where('title', $title)->sole()->slug)
        ->toBe(str_repeat('a', 252).'-10');
});

test('an administrator can save a post draft from the dashboard widget', function (): void {
    Post::factory()->create([
        'title' => 'Sensory-Friendly Park Notes',
        'slug' => 'sensory-friendly-park-notes',
    ]);
    actingAs(User::factory()->admin()->create());

    Livewire::test(QuickDraft::class)
        ->set('data.title', 'Sensory-Friendly Park Notes')
        ->set('data.notes', 'Ideas to develop for a future post.')
        ->call('saveDraft')
        ->assertNotified();

    $post = Post::query()->where('excerpt', 'Ideas to develop for a future post.')->sole();

    expect($post->title)->toBe('Sensory-Friendly Park Notes')
        ->and($post->slug)->toBe('sensory-friendly-park-notes-2')
        ->and($post->excerpt)->toBe('Ideas to develop for a future post.')
        ->and($post->body)->toBe('Ideas to develop for a future post.')
        ->and($post->author)->toBe(ContentAuthor::Both)
        ->and($post->is_published)->toBeFalse();
});
