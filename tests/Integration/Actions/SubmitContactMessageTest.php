<?php

use App\Actions\SubmitContactMessage;
use App\Jobs\SendContactMessageEmails;
use App\Models\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;

use function Pest\Laravel\assertDatabaseHas;

pest()->use(RefreshDatabase::class);

covers(SubmitContactMessage::class);

test('action stores a contact message and dispatches email delivery', function (): void {
    Bus::fake([SendContactMessageEmails::class]);

    $attributes = [
        'name' => 'Dale Cooper',
        'email' => 'dale@example.test',
        'subject' => 'general',
        'message' => 'Please help with this park question.',
    ];

    app(SubmitContactMessage::class)($attributes);

    assertDatabaseHas('contact_messages', $attributes);
    $message = ContactMessage::query()->sole();

    Bus::assertDispatched(
        SendContactMessageEmails::class,
        fn (SendContactMessageEmails $job): bool => $job->contactMessageId === $message->getKey(),
    );
});
