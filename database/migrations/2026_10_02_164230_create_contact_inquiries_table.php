<?php

use App\Enums\ContactInquiryStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Personal fields are text because they hold encrypted values.
     */
    public function up(): void
    {
        Schema::create('contact_inquiries', function (Blueprint $table): void {
            $table->id();
            $table->text('name');
            $table->text('email');
            $table->string('type', 32);
            $table->text('message');
            $table->enum('status', array_column(ContactInquiryStatus::cases(), 'value'))
                ->default(ContactInquiryStatus::New->value);
            $table->timestamps();
            $table->timestamp('email_attempted_at')->nullable();
            $table->timestamp('notification_sent_at')->nullable();
            $table->timestamp('confirmation_sent_at')->nullable();
            $table->index(['status', 'created_at']);
        });
    }
};
