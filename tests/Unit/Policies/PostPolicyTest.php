<?php

use App\Models\Post;
use App\Models\User;
use App\Policies\PostPolicy;
use Illuminate\Support\Carbon;

covers(PostPolicy::class);

test('post editorial abilities are limited to administrators', function (): void {
    $policy = new PostPolicy;
    $post = new Post;
    $admin = new User;
    $admin->setRawAttributes(['is_admin' => true]);
    $visitor = new User;
    $visitor->setRawAttributes(['is_admin' => false]);

    $adminResults = [
        $policy->viewAny($admin),
        $policy->view($admin, $post),
        $policy->create($admin),
        $policy->update($admin, $post),
        $policy->delete($admin, $post),
        $policy->deleteAny($admin),
        $policy->restore($admin, $post),
        $policy->restoreAny($admin),
        $policy->forceDelete($admin, $post),
        $policy->forceDeleteAny($admin),
    ];
    $visitorResults = [
        $policy->viewAny($visitor),
        $policy->view($visitor, $post),
        $policy->create($visitor),
        $policy->update($visitor, $post),
        $policy->delete($visitor, $post),
        $policy->deleteAny($visitor),
        $policy->restore($visitor, $post),
        $policy->restoreAny($visitor),
        $policy->forceDelete($visitor, $post),
        $policy->forceDeleteAny($visitor),
    ];

    expect($adminResults)->each->toBeTrue()
        ->and($visitorResults)->each->toBeFalse();
});

test('post public policy allows published posts and hides other posts', function (bool $published, string $publishedAt, bool $allowed, ?int $status): void {
    $post = new Post;
    $post->setRawAttributes([
        'is_published' => $published,
        'published_at' => Carbon::parse($publishedAt),
    ]);

    $response = (new PostPolicy)->viewPublic(null, $post);

    expect($response->allowed())->toBe($allowed)
        ->and($response->status())->toBe($status);
})->with([
    'published post' => [true, '2026-01-01', true, null],
    'draft post' => [false, '2026-01-01', false, 404],
    'scheduled post' => [true, '2099-01-01', false, 404],
]);
