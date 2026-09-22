<?php

use App\Models\Episode;
use App\Models\User;
use App\Policies\EpisodePolicy;
use Illuminate\Support\Carbon;

covers(EpisodePolicy::class);

test('episode editorial abilities are limited to administrators', function (): void {
    $policy = new EpisodePolicy;
    $episode = new Episode;
    $admin = new User;
    $admin->setRawAttributes(['is_admin' => true]);
    $visitor = new User;
    $visitor->setRawAttributes(['is_admin' => false]);

    $adminResults = [
        $policy->viewAny($admin),
        $policy->view($admin, $episode),
        $policy->create($admin),
        $policy->update($admin, $episode),
        $policy->delete($admin, $episode),
        $policy->deleteAny($admin),
        $policy->restore($admin, $episode),
        $policy->restoreAny($admin),
        $policy->forceDelete($admin, $episode),
        $policy->forceDeleteAny($admin),
    ];
    $visitorResults = [
        $policy->viewAny($visitor),
        $policy->view($visitor, $episode),
        $policy->create($visitor),
        $policy->update($visitor, $episode),
        $policy->delete($visitor, $episode),
        $policy->deleteAny($visitor),
        $policy->restore($visitor, $episode),
        $policy->restoreAny($visitor),
        $policy->forceDelete($visitor, $episode),
        $policy->forceDeleteAny($visitor),
    ];

    expect($adminResults)->each->toBeTrue()
        ->and($visitorResults)->each->toBeFalse();
});

test('episode public policy allows published episodes and hides other episodes', function (bool $published, string $publishedAt, bool $allowed, ?int $status): void {
    $episode = new Episode;
    $episode->setRawAttributes([
        'is_published' => $published,
        'published_at' => Carbon::parse($publishedAt),
    ]);

    $response = (new EpisodePolicy)->viewPublic(null, $episode);

    expect($response->allowed())->toBe($allowed)
        ->and($response->status())->toBe($status);
})->with([
    'published episode' => [true, '2026-01-01', true, null],
    'draft episode' => [false, '2026-01-01', false, 404],
    'scheduled episode' => [true, '2099-01-01', false, 404],
]);
