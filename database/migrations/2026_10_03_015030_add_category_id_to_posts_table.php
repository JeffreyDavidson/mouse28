<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Give posts a nullable `category_id` that references `categories` and is set to
     * NULL when its category is deleted (The Laravel Architect's column). Every step
     * is guarded so the migration can safely run again. `posts.category` stays until
     * a later release drops it.
     */
    public function up(): void
    {
        $this->addCategoryColumn();
        $this->addCategoryIndex();
        $this->addCategoryForeignKey();
    }

    private function addCategoryColumn(): void
    {
        if (Schema::hasColumn('posts', 'category_id')) {
            return;
        }

        if (DB::getDriverName() === 'sqlite') {
            // Not `foreignId()->constrained()`: on SQLite that rebuilds the posts table,
            // which can silently drop CHECK constraints. A nullable column with an inline
            // REFERENCES clause is added in place and carries the foreign key with it.
            DB::statement('ALTER TABLE posts ADD COLUMN category_id INTEGER NULL REFERENCES categories (id) ON DELETE SET NULL');

            return;
        }

        Schema::table('posts', function (Blueprint $table): void {
            $table->unsignedBigInteger('category_id')->nullable()->after('category');
        });
    }

    private function addCategoryIndex(): void
    {
        if (Schema::hasIndex('posts', ['category_id'])) {
            return;
        }

        Schema::table('posts', function (Blueprint $table): void {
            $table->index('category_id');
        });
    }

    /** SQLite received its foreign key with the column; other drivers add it here. */
    private function addCategoryForeignKey(): void
    {
        if (DB::getDriverName() === 'sqlite' || $this->hasCategoryForeignKey()) {
            return;
        }

        Schema::table('posts', function (Blueprint $table): void {
            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->nullOnDelete();
        });
    }

    private function hasCategoryForeignKey(): bool
    {
        return collect(Schema::getForeignKeys('posts'))
            ->contains(fn (array $foreignKey): bool => $foreignKey['columns'] === ['category_id']);
    }
};
