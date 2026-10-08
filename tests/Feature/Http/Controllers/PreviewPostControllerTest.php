<?php

use App\Models\Episode;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\URL;

use function Pest\Laravel\get;

pest()->use(RefreshDatabase::class);

test('a signed preview link shows the draft to anyone holding it without exposing it to search', function (): void {
    $post = Post::factory()
        ->draft()
        ->create();

    get(URL::temporarySignedRoute('preview.post', Date::now()->addHour(), ['post' => $post]))
        ->assertOk()
        ->assertViewIs('pages.blog.show')
        ->assertViewHas('post', fn (Post $viewPost): bool => $viewPost->is($post))
        ->assertViewHas('isPreview', true)
        ->assertSee('Preview mode')
        ->assertSeeHtml('noindex,nofollow')
        ->assertDontSeeHtml('application/ld+json')
        ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
});

test('preview links must be signed, untampered, and unexpired', function (string $case): void {
    $post = Post::factory()
        ->draft()
        ->create();
    $signed = URL::temporarySignedRoute('preview.post', Date::now()->addHour(), ['post' => $post]);
    $url = match ($case) {
        'unsigned' => route('preview.post', $post),
        'tampered' => "{$signed}0",
        default => $signed,
    };

    if ($case === 'expired') {
        Date::setTestNow(Date::now()->addHours(2));
    }

    get($url)->assertForbidden();
})->with(['unsigned', 'tampered', 'expired']);

test('the former numeric preview address no longer shows the draft', function (): void {
    $post = Post::factory()
        ->draft()
        ->create();

    expect(get("/preview/posts/{$post->id}")->status())->toBeIn([403, 404]);
});

test('a signed preview links every related episode, published or not', function (): void {
    $post = Post::factory()
        ->draft()
        ->create();
    $post->episodes()
        ->attach([
            Episode::factory()
                ->draft()
                ->create(['title' => 'Unannounced podcast episode'])
                ->id,
            Episode::factory()
                ->create(['title' => 'Released podcast episode'])
                ->id,
        ]);

    get(URL::temporarySignedRoute('preview.post', Date::now()->addHour(), ['post' => $post]))
        ->assertOk()
        ->assertSee('Unannounced podcast episode')
        ->assertSee('Released podcast episode');
});

test('share links on a preview point to the public post, never the signed preview link', function (): void {
    $post = Post::factory()
        ->draft()
        ->create();
    $previewUrl = URL::temporarySignedRoute('preview.post', Date::now()->addHour(), ['post' => $post]);

    $response = get($previewUrl);

    $response->assertOk()
        ->assertSeeHtml('u='.urlencode(route('blog.show', $post)))
        ->assertSeeHtml('url='.urlencode(route('blog.show', $post)))
        ->assertDontSeeHtml(urlencode(route('preview.post', $post)));
});
