<?php

use App\Models\Episode;
use App\Models\Post;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

pest()->use(RefreshDatabase::class);

function runEpisodePostTableMigration(): void
{
    $migration = require database_path('migrations/2026_10_02_235304_create_episode_post_table.php');

    if (! $migration instanceof Migration || ! method_exists($migration, 'up')) {
        throw new UnexpectedValueException('The episode post table migration could not be loaded.');
    }

    $migration->up();
}

test('the pivot holds only the post and episode keys under a composite primary key', function (): void {
    expect(Schema::getColumnListing('episode_post'))->toEqualCanonicalizing(['post_id', 'episode_id'])
        ->and(Schema::hasIndex('episode_post', ['post_id', 'episode_id'], 'primary'))->toBeTrue();
});

test('the pivot cascades deletes from both sides', function (): void {
    $onDelete = array_column(Schema::getForeignKeys('episode_post'), 'on_delete', 'foreign_table');

    expect($onDelete)->toEqualCanonicalizing(['posts' => 'cascade', 'episodes' => 'cascade']);
});

test('running the pivot migration again keeps the table and its links', function (): void {
    $post = Post::factory()->create();
    $post->episodes()->attach(Episode::factory()->create());

    runEpisodePostTableMigration();

    expect(DB::table('episode_post')->count())->toBe(1);
});
