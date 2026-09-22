<?php

use App\Filament\Resources\Episodes\EpisodeResource;
use App\Filament\Resources\Posts\PostResource;
use App\Models\Episode;
use App\Models\Post;
use App\Models\User;
use Filament\Pages\Dashboard;

use function Pest\Laravel\actingAs;

test('administrator can save a Quick Draft from the dashboard', function (): void {
    actingAs(User::factory()->admin()->create());

    visit(Dashboard::getUrl(panel: 'admin'))
        ->fill('input[placeholder="Post title..."]', 'Browser Smoke Draft')
        ->fill('textarea[placeholder="Quick notes or ideas..."]', 'Browser smoke test notes.')
        ->click('button:has-text("Save Draft")')
        ->assertSee('Draft saved!')
        ->assertNoJavaScriptErrors();

    visit(PostResource::getUrl())
        ->assertSee('Browser Smoke Draft')
        ->assertNoJavaScriptErrors();
})->group('browser-smoke');

test('administrator can create a Post from the resource form', function (): void {
    actingAs(User::factory()->admin()->create());

    visit(PostResource::getUrl('create'))
        ->fill('input[id="form.title"]', 'Browser Smoke Post')
        ->fill('input[id="form.slug"]', 'browser-smoke-post')
        ->select('select[id="form.category"]', 'disney-tips')
        ->fill('textarea[aria-label="Body"]', 'A browser-created post body.')
        ->click('button[wire\\:target="create"]')
        ->assertSee('Created')
        ->assertNoJavaScriptErrors();

    visit(PostResource::getUrl())
        ->assertSee('Browser Smoke Post')
        ->assertNoJavaScriptErrors();
})->group('browser-smoke');

test('administrator can edit a Post from the resource form', function (): void {
    $post = Post::factory()->draft()->create();

    actingAs(User::factory()->admin()->create());

    visit(PostResource::getUrl('edit', ['record' => $post]))
        ->fill('input[id="form.title"]', 'Updated Browser Post')
        ->fill('input[id="form.slug"]', 'updated-browser-post')
        ->click('button[wire\\:target="save"]')
        ->assertSee('Saved')
        ->assertNoJavaScriptErrors();

    visit(PostResource::getUrl())
        ->assertSee('Updated Browser Post')
        ->assertNoJavaScriptErrors();
})->group('browser-smoke');

test('administrator can create an Episode from the resource form', function (): void {
    actingAs(User::factory()->admin()->create());

    visit(EpisodeResource::getUrl('create'))
        ->fill('input[id="form.title"]', 'Browser Smoke Episode')
        ->fill('input[id="form.slug"]', 'browser-smoke-episode')
        ->fill('input[id="form.episode_number"]', '280')
        ->click('button[wire\\:target="create"]')
        ->assertSee('Created')
        ->assertNoJavaScriptErrors();

    visit(EpisodeResource::getUrl())
        ->assertSee('Browser Smoke Episode')
        ->assertNoJavaScriptErrors();
})->group('browser-smoke');

test('administrator can edit an Episode from the resource form', function (): void {
    $episode = Episode::factory()->draft()->create();

    actingAs(User::factory()->admin()->create());

    visit(EpisodeResource::getUrl('edit', ['record' => $episode]))
        ->fill('input[id="form.title"]', 'Updated Browser Episode')
        ->fill('input[id="form.slug"]', 'updated-browser-episode')
        ->click('button[wire\\:target="save"]')
        ->assertSee('Saved')
        ->assertNoJavaScriptErrors();

    visit(EpisodeResource::getUrl())
        ->assertSee('Updated Browser Episode')
        ->assertNoJavaScriptErrors();
})->group('browser-smoke');

test('Post creation shows required category validation while preserving entered values', function (): void {
    actingAs(User::factory()->admin()->create());

    visit(PostResource::getUrl('create'))
        ->fill('input[id="form.title"]', 'Browser Validation Post')
        ->fill('input[id="form.slug"]', 'browser-validation-post')
        ->click('button[wire\\:target="create"]')
        ->assertScript('document.activeElement.id', 'form.category')
        ->assertSee('Create Post')
        ->assertDontSee('Created')
        ->assertValue('input[id="form.title"]', 'Browser Validation Post')
        ->assertNoJavaScriptErrors();
})->group('browser-smoke');

test('Episode creation shows duplicate number validation while preserving entered values', function (): void {
    Episode::factory()->create(['episode_number' => 280]);
    actingAs(User::factory()->admin()->create());

    visit(EpisodeResource::getUrl('create'))
        ->fill('input[id="form.title"]', 'Browser Duplicate Episode')
        ->fill('input[id="form.slug"]', 'browser-duplicate-episode')
        ->fill('input[id="form.episode_number"]', '280')
        ->click('button[wire\\:target="create"]')
        ->assertSee('The episode number has already been taken.')
        ->assertValue('input[id="form.title"]', 'Browser Duplicate Episode')
        ->assertNoJavaScriptErrors();
})->group('browser-smoke');
