<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Let a user be credited as a post or guide author (`is_author`) and give them a
     * short public bio. Both are plain column additions, so SQLite adds them in place
     * without rebuilding `users`. Each column is only added when missing, so the
     * migration can safely run again.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'is_author')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->boolean('is_author')->default(false)->after('is_admin');
            });
        }

        if (! Schema::hasColumn('users', 'bio')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->text('bio')->nullable()->after('is_author');
            });
        }
    }
};
