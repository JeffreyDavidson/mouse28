<?php

use App\Models\User;
use Laravel\Nightwatch\Core;

test('Nightwatch identifies administrators without sending their profile details', function (): void {
    $resolver = app(Core::class)->userDetailsResolver;

    $userDetails = $resolver(User::factory()->make());

    expect($userDetails)->toBe([]);
});
