<?php

use App\Models\User;
use Laravel\Nightwatch\Core;

test('Nightwatch identifies administrators without sending their profile details', function (): void {
    $admin = User::factory()->admin()->make();
    $resolver = app(Core::class)->userDetailsResolver;

    $userDetails = $resolver($admin);

    expect($userDetails)->toBe([]);
});
