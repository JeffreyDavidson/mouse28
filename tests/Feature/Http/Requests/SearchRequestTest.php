<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\from;

uses(RefreshDatabase::class);

test('search query is limited to one hundred characters', function (): void {
    from(route('search'))
        ->get(route('search', ['q' => str_repeat('a', 101)]))
        ->assertRedirect(route('search'))
        ->assertSessionHasErrors('q');
});
