<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('community_stories');
    }

    public function down(): void
    {
        // Not recreating — feature removed
    }
};
