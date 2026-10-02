<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @var list<string> */
    private const array TABLES = ['posts', 'guides'];

    /**
     * Give posts and guides a `content` column (The Laravel Architect's name for the
     * written content) and copy the legacy `body` into it. Every step is guarded so
     * the migration can safely run again: the column is only added when missing,
     * and the copy only fills rows whose content is still NULL, including
     * soft-deleted rows. `content` is nullable (TLA's is NOT NULL) so it can be
     * added without a table rebuild; `body` stays until a later release drops it.
     */
    public function up(): void
    {
        foreach (self::TABLES as $tableName) {
            $this->addContentColumn($tableName);
            $this->copyBodyIntoContent($tableName);
            $this->assertEveryBodyWasCopied($tableName);
        }
    }

    private function addContentColumn(string $tableName): void
    {
        if (Schema::hasColumn($tableName, 'content')) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table): void {
            $table->longText('content')->nullable()->after('body');
        });
    }

    private function copyBodyIntoContent(string $tableName): void
    {
        DB::table($tableName)
            ->whereNull('content')
            ->update(['content' => DB::raw('body')]);
    }

    /**
     * Refuse to finish while any row still holds written text only in `body`, so
     * the legacy column can never be dropped while it holds text `content` lacks.
     */
    private function assertEveryBodyWasCopied(string $tableName): void
    {
        $uncopied = DB::table($tableName)
            ->where('body', '<>', '')
            ->where(function (Builder $query): void {
                $query->whereNull('content')->orWhere('content', '');
            })
            ->count();

        if ($uncopied > 0) {
            throw new RuntimeException("{$uncopied} {$tableName} row(s) still have a body but no content after the backfill.");
        }
    }
};
