<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

pest()->use(RefreshDatabase::class);

function runUserAuthorFieldsMigration(): void
{
    $migration = require database_path('migrations/2026_10_03_144331_add_author_fields_to_users_table.php');

    if (! $migration instanceof Migration || ! method_exists($migration, 'up')) {
        throw new UnexpectedValueException('The user author fields migration could not be loaded.');
    }

    $migration->up();
}

test('users gain a non-null author flag and a nullable bio', function (): void {
    $nullable = array_column(Schema::getColumns('users'), 'nullable', 'name');

    expect($nullable['is_author'])->toBeFalse()
        ->and($nullable['bio'])->toBeTrue();
});

test('a user inserted without the new columns is not an author and has no bio', function (): void {
    $id = DB::table('users')->insertGetId([
        'name' => 'Sample User',
        'email' => 'sample-user@example.test',
        'password' => 'not-a-real-hash',
    ]);

    expect(DB::table('users')->where('id', $id)->first(['is_author', 'bio']))
        ->is_author->toEqual(0)
        ->bio->toBeNull();
});

test('running the user author fields migration again keeps each user\'s author flag and bio', function (): void {
    $user = User::factory()->author()->create(['bio' => 'Sample bio.']);

    runUserAuthorFieldsMigration();

    expect($user->refresh())
        ->is_author->toBeTrue()
        ->bio->toBe('Sample bio.');
});
