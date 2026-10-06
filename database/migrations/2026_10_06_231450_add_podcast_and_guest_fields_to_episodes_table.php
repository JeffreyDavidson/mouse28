<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Link every episode to its podcast (`podcast_id`, cascading on delete like the
     * Laravel Architect's) and add its nullable guest fields. Existing episodes join the
     * one podcast, which is created with the site defaults if it does not exist yet; with
     * several podcasts the migration refuses to guess. Safe to run again.
     */
    public function up(): void
    {
        $this->addPodcastColumn();

        if (! Schema::hasColumn('episodes', 'guest_name')) {
            Schema::table('episodes', function (Blueprint $table): void {
                $table->string('guest_name')->nullable();
                $table->string('guest_title')->nullable();
                $table->string('guest_url')->nullable();
            });
        }

        $this->attachEpisodesToThePodcast();
    }

    private function addPodcastColumn(): void
    {
        if (Schema::hasColumn('episodes', 'podcast_id')) {
            return;
        }

        // The schema builder rebuilds the table on SQLite to add a foreign key, which drops
        // the status CHECK constraint; a plain ALTER keeps it. MySQL needs the builder,
        // because it ignores an inline REFERENCES clause.
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('ALTER TABLE episodes ADD COLUMN podcast_id INTEGER NULL REFERENCES podcasts (id) ON DELETE CASCADE');

            return;
        }

        Schema::table('episodes', function (Blueprint $table): void {
            $table->foreignId('podcast_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });
    }

    private function attachEpisodesToThePodcast(): void
    {
        if (! DB::table('episodes')->whereNull('podcast_id')->exists()) {
            return;
        }

        $podcastIds = DB::table('podcasts')->pluck('id');

        if ($podcastIds->count() > 1) {
            throw new RuntimeException('Episodes without a podcast cannot be attached automatically while several podcasts exist.');
        }

        $podcastId = $podcastIds->first() ?? DB::table('podcasts')->insertGetId([
            'name' => 'Mouse28',
            'slug' => 'mouse28',
            'description' => 'Disney parks through the lens of raising a daughter with autism.',
            'is_active' => true,
            'sort_order' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('episodes')->whereNull('podcast_id')->update(['podcast_id' => $podcastId]);
    }
};
