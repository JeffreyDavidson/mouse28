<?php

use App\Enums\PublishStatus;
use App\Filament\Widgets\QuickDraft;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

test('initializes the quick draft form state for Livewire entanglement', function (): void {
    actingAs(User::factory()->admin()->create());

    livewire(QuickDraft::class)
        ->assertSet('data.title', null)
        ->assertSet('data.notes', null);
});

test('duplicate maximum length titles keep generated slugs within the column limit', function (): void {
    $title = str_repeat('a', 255);
    Post::factory()->create(['slug' => $title]);
    foreach (range(2, 9) as $suffix) {
        Post::factory()->create(['slug' => str_repeat('a', 253)."-{$suffix}"]);
    }
    actingAs(User::factory()->admin()->create());

    livewire(QuickDraft::class)
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

    livewire(QuickDraft::class)
        ->set('data.title', 'Sensory-Friendly Park Notes')
        ->set('data.notes', 'Ideas to develop for a future post.')
        ->call('saveDraft')
        ->assertNotified();

    $post = Post::query()->where('excerpt', 'Ideas to develop for a future post.')->sole();

    expect($post->title)->toBe('Sensory-Friendly Park Notes')
        ->and($post->slug)->toBe('sensory-friendly-park-notes-2')
        ->and($post->excerpt)->toBe('Ideas to develop for a future post.')
        ->and($post->content)->toBe('Ideas to develop for a future post.')
        ->and($post->authors->pluck('name')->all())->toBe(['Jeffrey Davidson', 'Cassie Davidson'])
        ->and($post->author_name)->toBe('Jeffrey & Cassie')
        ->and($post->status)->toBe(PublishStatus::Draft);
});

test('a draft title with no letters or numbers is rejected because it cannot name the post', function (): void {
    actingAs(User::factory()->admin()->create());

    livewire(QuickDraft::class)
        ->set('data.title', '!!!')
        ->call('saveDraft')
        ->assertNotNotified()
        ->assertHasErrors(['title']);

    expect(Post::query()->count())->toBe(0);
});
