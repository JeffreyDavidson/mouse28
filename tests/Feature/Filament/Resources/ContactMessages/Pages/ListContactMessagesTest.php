<?php

use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

test('authenticated user can render the resource listing', function (): void {
    actingAs(User::factory()->admin()->create());

    get(ContactMessageResource::getUrl())
        ->assertOk()
        ->assertSee('Contact Messages');
});
