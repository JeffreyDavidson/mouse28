<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

test('non admin user cannot access the admin panel', function (): void {
    $user = User::factory()->create();

    actingAs($user)
        ->get('/admin')
        ->assertForbidden();
});
