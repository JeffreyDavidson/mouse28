<?php

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\URL;

use function Pest\Laravel\get;

pest()->use(RefreshDatabase::class);

test('a signed preview link shows the draft to anyone holding it without exposing it to search', function (): void {
    $post = Post::factory()->draft()->create();

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
    $post = Post::factory()->draft()->create();
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
    $post = Post::factory()->draft()->create();

    expect(get("/preview/posts/{$post->id}")->status())->toBeIn([403, 404]);
});
