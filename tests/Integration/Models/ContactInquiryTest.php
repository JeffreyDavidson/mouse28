<?php

use App\Enums\ContactInquiryStatus;
use App\Enums\ContactType;
use App\Models\ContactInquiry;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

pest()->use(RefreshDatabase::class);

test('contact inquiries encrypt the sender name, email and message at rest', function (): void {
    $inquiry = ContactInquiry::factory()->create([
        'name' => 'Dale Cooper',
        'email' => 'dale@example.com',
        'message' => 'A question about visiting the parks.',
    ]);

    $raw = DB::table('contact_inquiries')
        ->where('id', $inquiry->id)
        ->first(['name', 'email', 'message']);
    $inquiry->refresh();

    expect((array) $raw)->each->not->toBeIn(['Dale Cooper', 'dale@example.com', 'A question about visiting the parks.'])
        ->and($inquiry->name)
        ->toBe('Dale Cooper')
        ->and($inquiry->email)
        ->toBe('dale@example.com')
        ->and($inquiry->message)
        ->toBe('A question about visiting the parks.');
});

test('contact inquiries store their type and default to the new status', function (ContactType $type): void {
    $inquiry = ContactInquiry::query()->create([
        'name' => 'Dale Cooper',
        'email' => 'dale@example.com',
        'type' => $type,
        'message' => 'A question about visiting the parks.',
    ]);

    $inquiry->refresh();

    expect($inquiry->type)->toBe($type)
        ->and($inquiry->status)
        ->toBe(ContactInquiryStatus::New)
        ->and(DB::table('contact_inquiries')->value('type'))
        ->toBe($type->value);
})->with(ContactType::cases());

test('contact inquiries allow email retries only within 23 hours of submission', function (int $minutesAgo, bool $canRetry): void {
    Date::setTestNow('2026-10-02 12:00:00');
    $inquiry = ContactInquiry::factory()->create(['created_at' => Date::now()->subMinutes($minutesAgo)]);

    expect($inquiry->canRetryEmails())->toBe($canRetry);
})->with([
    'just submitted' => [0, true],
    'exactly 23 hours' => [23 * 60, true],
    'one minute past 23 hours' => [23 * 60 + 1, false],
]);

test('contact inquiries without a submission time cannot retry emails', function (): void {
    $inquiry = new ContactInquiry;

    expect($inquiry->canRetryEmails())->toBeFalse();
});

test('contact inquiries are kept until an administrator deletes them', function (): void {
    expect(class_uses_recursive(ContactInquiry::class))->not->toContain(Prunable::class);
});

test('reply links percent-encode the address and type label for mail clients', function (string $email, ContactType $type, string $url): void {
    $inquiry = ContactInquiry::factory()->make([
        'email' => $email,
        'type' => $type,
    ]);

    expect($inquiry->replyMailtoUrl())->toBe($url);
})->with([
    'spaces in the subject' => ['dale@example.com', ContactType::General, 'mailto:dale@example.com?subject=Re%3A%20General%20Question'],
    'query delimiters in the address' => ['dale?cc=x&y@example.com', ContactType::Other, 'mailto:dale%3Fcc%3Dx%26y@example.com?subject=Re%3A%20Other'],
]);

test('the new scope returns only inquiries that have not been opened', function (): void {
    $new = ContactInquiry::factory()->create(['status' => ContactInquiryStatus::New]);
    ContactInquiry::factory()->create(['status' => ContactInquiryStatus::InProgress]);
    ContactInquiry::factory()->create(['status' => ContactInquiryStatus::Resolved]);

    $ids = ContactInquiry::query()
        ->new()
        ->pluck('id')
        ->all();

    expect($ids)->toBe([$new->id]);
});

test('an inquiry knows whether it is new', function (ContactInquiryStatus $status, bool $expected): void {
    $inquiry = ContactInquiry::factory()->make(['status' => $status]);

    expect($inquiry->isNew())->toBe($expected);
})->with([
    'new' => [ContactInquiryStatus::New, true],
    'in progress' => [ContactInquiryStatus::InProgress, false],
    'resolved' => [ContactInquiryStatus::Resolved, false],
]);
