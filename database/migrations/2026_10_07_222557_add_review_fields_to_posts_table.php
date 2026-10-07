<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Give posts The Laravel Architect's review fields: nullable `review_notes`, a
     * `reviewed_by` user that is set to NULL when that user is deleted, and
     * `reviewed_at`. Every step is guarded so the migration can safely run again.
     */
    public function up(): void
    {
        $this->addReviewNotesColumn();
        $this->addReviewerColumn();
        $this->addReviewedAtColumn();
        $this->addReviewerForeignKey();
    }

    private function addReviewNotesColumn(): void
    {
        if (Schema::hasColumn('posts', 'review_notes')) {
            return;
        }

        Schema::table('posts', function (Blueprint $table): void {
            $table->text('review_notes')->nullable()->after('content');
        });
    }

    private function addReviewerColumn(): void
    {
        if (Schema::hasColumn('posts', 'reviewed_by')) {
            return;
        }

        if (DB::getDriverName() === 'sqlite') {
            // Not `foreign()`: on SQLite that rebuilds the posts table, which can silently
            // drop CHECK constraints. A nullable column with an inline REFERENCES clause is
            // added in place and carries the foreign key with it.
            DB::statement('ALTER TABLE posts ADD COLUMN reviewed_by INTEGER NULL REFERENCES users (id) ON DELETE SET NULL');

            return;
        }

        Schema::table('posts', function (Blueprint $table): void {
            $table->unsignedBigInteger('reviewed_by')->nullable()->after('review_notes');
        });
    }

    private function addReviewedAtColumn(): void
    {
        if (Schema::hasColumn('posts', 'reviewed_at')) {
            return;
        }

        Schema::table('posts', function (Blueprint $table): void {
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
        });
    }

    /** SQLite received its foreign key with the column; other drivers add it here. */
    private function addReviewerForeignKey(): void
    {
        if (DB::getDriverName() === 'sqlite' || $this->hasReviewerForeignKey()) {
            return;
        }

        Schema::table('posts', function (Blueprint $table): void {
            $table->foreign('reviewed_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    private function hasReviewerForeignKey(): bool
    {
        return collect(Schema::getForeignKeys('posts'))
            ->contains(fn (array $foreignKey): bool => $foreignKey['columns'] === ['reviewed_by']);
    }
};
