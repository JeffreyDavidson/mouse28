<?php

use App\Models\Podcast;
use App\Models\User;
use App\Policies\PodcastPolicy;

covers(PodcastPolicy::class);

test('podcast editorial abilities are limited to administrators', function (): void {
    $policy = new PodcastPolicy;
    $podcast = new Podcast;
    $admin = new User;
    $admin->setRawAttributes(['is_admin' => true]);
    $visitor = new User;
    $visitor->setRawAttributes(['is_admin' => false]);

    expect($policy->update($admin, $podcast))->toBeTrue()
        ->and($policy->update($visitor, $podcast))->toBeFalse();
});
