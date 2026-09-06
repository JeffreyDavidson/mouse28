<?php

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

test('preview routes reject guests and non admin users', function (): void {
    $post = Post::factory()->draft()->create();

    get(route('preview.posts', $post))->assertForbidden();

    actingAs(User::factory()->create())
        ->get(route('preview.posts', $post))
        ->assertForbidden();
});

test('administrators can preview draft content without exposing structured data', function (): void {
    $admin = User::factory()->admin()->create();
    $post = Post::factory()->draft()->create();

    actingAs($admin);

    get(route('preview.posts', $post))
        ->assertOk()
        ->assertSee('Preview mode')
        ->assertSee('noindex,nofollow', false)
        ->assertDontSee('application/ld+json', false);
});
