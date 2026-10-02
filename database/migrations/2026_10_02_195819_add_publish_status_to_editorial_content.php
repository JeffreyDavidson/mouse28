<?php

use App\Enums\PublishStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @var list<string> */
    private const array TABLES = ['posts', 'episodes', 'guides', 'newsletter_issues'];

    /**
     * Give editorial content a persisted publish status (The Laravel Architect's
     * shape) and backfill it from the legacy `is_published` flag and publish date.
     * Every step is guarded so the migration can safely run again: the column and
     * index are only added when missing, and the backfill only touches rows still
     * at the default draft status, including soft-deleted rows. `is_published`
     * stays until a later release drops it.
     */
    public function up(): void
    {
        foreach (self::TABLES as $tableName) {
            $this->addStatusColumn($tableName);
            $this->backfillStatus($tableName);
            $this->assertEveryPublishedRowHasAStatus($tableName);
        }
    }

    private function addStatusColumn(string $tableName): void
    {
        if (! Schema::hasColumn($tableName, 'status')) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->string('status')->default(PublishStatus::Draft->value);
            });
        }

        if (! Schema::hasIndex($tableName, ['status', 'published_at'])) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->index(['status', 'published_at']);
            });
        }
    }

    /**
     * Unpublished rows keep the default draft status. Published rows with no
     * publish date become published on their creation date (owner decision B).
     */
    private function backfillStatus(string $tableName): void
    {
        $now = Date::now();

        DB::table($tableName)
            ->where('status', PublishStatus::Draft->value)
            ->where('is_published', true)
            ->whereNull('published_at')
            ->update([
                'status' => PublishStatus::Published->value,
                'published_at' => DB::raw('created_at'),
            ]);

        DB::table($tableName)
            ->where('status', PublishStatus::Draft->value)
            ->where('is_published', true)
            ->where('published_at', '>', $now)
            ->update(['status' => PublishStatus::Scheduled->value]);

        DB::table($tableName)
            ->where('status', PublishStatus::Draft->value)
            ->where('is_published', true)
            ->where('published_at', '<=', $now)
            ->update(['status' => PublishStatus::Published->value]);
    }

    /**
     * Refuse to finish while any published row is still a draft, so the legacy
     * flag can never be dropped while it holds information the status lacks.
     */
    private function assertEveryPublishedRowHasAStatus(string $tableName): void
    {
        $unconverted = DB::table($tableName)
            ->where('is_published', true)
            ->where('status', PublishStatus::Draft->value)
            ->count();

        if ($unconverted > 0) {
            throw new RuntimeException("{$unconverted} published {$tableName} row(s) still have the draft status after the backfill.");
        }
    }
};
