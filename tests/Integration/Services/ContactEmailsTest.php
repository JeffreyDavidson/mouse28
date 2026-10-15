<?php

use App\Mail\ContactMessageConfirmation;
use App\Mail\ContactMessageReceived;
use App\Models\ContactInquiry;
use App\Models\Subscriber;
use App\Services\ContactEmails;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JeffreyDavidson\CreatorKit\Contracts\ContactMails;

covers(ContactEmails::class);

pest()->use(RefreshDatabase::class);

test('creator-kit gets Mouse28 contact emails', function (): void {
    expect(app(ContactMails::class)::class)->toBe(ContactEmails::class);
});

test('the notification and confirmation are Mouse28 emails for the inquiry, with its idempotency keys', function (): void {
    // Arrange
    $inquiry = ContactInquiry::factory()->create();

    // Act
    $notification = new ContactEmails()->notification($inquiry);
    $confirmation = new ContactEmails()->confirmation($inquiry);

    // Assert
    expect($notification->inquiry->is($inquiry))->toBeTrue()
        ->and($notification->headers()
            ->text)
        ->toBe(new ContactMessageReceived($inquiry)->headers()
            ->text)
        ->and($confirmation->headers()
            ->text)
        ->toBe(new ContactMessageConfirmation($inquiry)->headers()
            ->text);
});

test('contact emails refuse a model that is not a contact inquiry', function (): void {
    expect(fn () => new ContactEmails()->notification(new Subscriber))
        ->toThrow(LogicException::class, 'Mouse28\'s contact emails need a ContactInquiry.')
        ->and(fn () => new ContactEmails()->confirmation(new Subscriber))
        ->toThrow(LogicException::class, 'Mouse28\'s contact emails need a ContactInquiry.');
});
