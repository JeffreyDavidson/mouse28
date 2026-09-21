<?php

use App\Models\Guide;
use App\Models\User;
use App\Policies\GuidePolicy;
use Illuminate\Support\Carbon;

covers(GuidePolicy::class);

test('guide editorial abilities are limited to administrators', function (): void {
    $policy = new GuidePolicy;
    $guide = new Guide;
    $admin = new User;
    $admin->setRawAttributes(['is_admin' => true]);
    $visitor = new User;
    $visitor->setRawAttributes(['is_admin' => false]);

    $adminResults = [
        $policy->viewAny($admin),
        $policy->view($admin, $guide),
        $policy->create($admin),
        $policy->update($admin, $guide),
        $policy->delete($admin, $guide),
        $policy->deleteAny($admin),
        $policy->restore($admin, $guide),
        $policy->restoreAny($admin),
        $policy->forceDelete($admin, $guide),
        $policy->forceDeleteAny($admin),
    ];
    $visitorResults = [
        $policy->viewAny($visitor),
        $policy->view($visitor, $guide),
        $policy->create($visitor),
        $policy->update($visitor, $guide),
        $policy->delete($visitor, $guide),
        $policy->deleteAny($visitor),
        $policy->restore($visitor, $guide),
        $policy->restoreAny($visitor),
        $policy->forceDelete($visitor, $guide),
        $policy->forceDeleteAny($visitor),
    ];

    expect($adminResults)->each->toBeTrue()
        ->and($visitorResults)->each->toBeFalse();
});

test('guide public policy allows published guides and hides other guides', function (bool $published, string $publishedAt, bool $allowed, ?int $status): void {
    $guide = new Guide;
    $guide->setRawAttributes([
        'is_published' => $published,
        'published_at' => Carbon::parse($publishedAt),
    ]);

    $response = (new GuidePolicy)->viewPublic(null, $guide);

    expect($response->allowed())->toBe($allowed)
        ->and($response->status())->toBe($status);
})->with([
    'published guide' => [true, '2026-01-01', true, null],
    'draft guide' => [false, '2026-01-01', false, 404],
    'scheduled guide' => [true, '2099-01-01', false, 404],
]);
