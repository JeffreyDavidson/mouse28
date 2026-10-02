<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscribers', function (Blueprint $table): void {
            $table->timestamp('suppressed_at')->nullable()->after('unsubscribed_at');
            $table->string('suppression_reason')->nullable()->after('suppressed_at');
        });
    }
};
