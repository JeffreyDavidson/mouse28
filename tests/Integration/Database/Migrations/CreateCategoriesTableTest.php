<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

pest()->use(RefreshDatabase::class);

function runCategoriesTableMigration(): void
{
    $migration = require database_path('migrations/2026_10_03_015027_create_categories_table.php');

    if (! $migration instanceof Migration || ! method_exists($migration, 'up')) {
        throw new UnexpectedValueException('The categories table migration could not be loaded.');
    }

    $migration->up();
}

test('the categories table holds a name, a unique slug and an optional description', function (): void {
    $nullable = array_column(Schema::getColumns('categories'), 'nullable', 'name');

    expect(Schema::getColumnListing('categories'))->toEqualCanonicalizing(['id', 'name', 'slug', 'description', 'created_at', 'updated_at'])
        ->and($nullable['name'])->toBeFalse()
        ->and($nullable['slug'])->toBeFalse()
        ->and($nullable['description'])->toBeTrue()
        ->and(Schema::hasIndex('categories', ['slug'], 'unique'))->toBeTrue();
});

test('the categories table rejects a duplicate slug', function (): void {
    DB::table('categories')->insert(['name' => 'First', 'slug' => 'duplicate-slug']);

    expect(fn () => DB::table('categories')->insert(['name' => 'Second', 'slug' => 'duplicate-slug']))
        ->toThrow(UniqueConstraintViolationException::class);
});

test('running the categories table migration again keeps the table and its rows', function (): void {
    $rows = DB::table('categories')->count();
    DB::table('categories')->insert(['name' => 'Kept', 'slug' => 'kept-category']);

    runCategoriesTableMigration();

    expect(DB::table('categories')->count())->toBe($rows + 1);
});
