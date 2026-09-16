<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contact_messages', function (Blueprint $table): void {
            $table->timestamp('email_attempted_at')->nullable();
            $table->timestamp('notification_sent_at')->nullable();
            $table->timestamp('confirmation_sent_at')->nullable();
        });
    }
};
