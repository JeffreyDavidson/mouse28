<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('episodes', function (Blueprint $table): void {
            $table->string('transistor_url')->nullable()->after('season_number');
        });

        DB::table('episodes')
            ->where('slug', 'trailer-meet-mouse28')
            ->update(['transistor_url' => 'https://share.transistor.fm/s/3db78f09']);

        DB::table('episodes')
            ->where('slug', 'meet-jeffrey-and-cassie-our-disney-story')
            ->update(['transistor_url' => 'https://share.transistor.fm/s/428d650c']);
    }
};
