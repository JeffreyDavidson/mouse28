<?php

use App\Enums\PublishStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @var list<string> */
    private const array PUBLISHED_FLAG_TABLES = ['posts', 'episodes', 'guides', 'newsletter_issues'];

    /** @var list<string> */
    private const array BODY_TABLES = ['posts', 'guides'];

    /**
     * Drop the legacy `is_published` flag (replaced by `status`) and the legacy
     * `body` text (replaced by `content`). Every table is checked before any column
     * is dropped, so a row that still holds information only in a legacy column
     * stops the migration with nothing changed. Each drop is skipped when the
     * column is already gone, so the migration can safely run again.
     */
    public function up(): void
    {
        foreach (self::PUBLISHED_FLAG_TABLES as $tableName) {
            $this->assertNoPublishedRowIsADraft($tableName);
        }

        foreach (self::BODY_TABLES as $tableName) {
            $this->assertNoTextOnlyInBody($tableName);
        }

        foreach (self::PUBLISHED_FLAG_TABLES as $tableName) {
            $this->dropColumn($tableName, 'is_published');
        }

        foreach (self::BODY_TABLES as $tableName) {
            $this->dropColumn($tableName, 'body');
        }
    }

    private function assertNoPublishedRowIsADraft(string $tableName): void
    {
        if (! Schema::hasColumn($tableName, 'is_published')) {
            return;
        }

        $drafts = DB::table($tableName)
            ->where('is_published', true)
            ->where('status', PublishStatus::Draft->value)
            ->count();

        if ($drafts > 0) {
            throw new RuntimeException("{$drafts} published {$tableName} row(s) still have the draft status.");
        }
    }

    private function assertNoTextOnlyInBody(string $tableName): void
    {
        if (! Schema::hasColumn($tableName, 'body')) {
            return;
        }

        $uncopied = DB::table($tableName)
            ->where('body', '<>', '')
            ->where(function (Builder $query): void {
                $query->whereNull('content')->orWhere('content', '');
            })
            ->count();

        if ($uncopied > 0) {
            throw new RuntimeException("{$uncopied} {$tableName} row(s) still have a body but no content.");
        }
    }

    private function dropColumn(string $tableName, string $column): void
    {
        if (! Schema::hasColumn($tableName, $column)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($column): void {
            $table->dropColumn($column);
        });
    }
};
