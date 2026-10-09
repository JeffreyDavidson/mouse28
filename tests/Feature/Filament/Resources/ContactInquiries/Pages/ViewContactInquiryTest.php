<?php

use App\Enums\ContactInquiryStatus;
use App\Enums\ContactType;
use App\Filament\Resources\ContactInquiries\ContactInquiryResource;
use App\Filament\Resources\ContactInquiries\Pages\ViewContactInquiry;
use App\Jobs\SendContactInquiryEmails;
use App\Models\ContactInquiry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Event;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

test('authenticated user can render the resource page', function (): void {
    $inquiry = ContactInquiry::factory()->create(['message' => 'A park question.', 'type' => ContactType::Guest]);

    actingAsAdmin();

    get(ContactInquiryResource::getUrl('view', ['record' => $inquiry]))
        ->assertOk()
        ->assertSee('A park question.')
        ->assertSee('Podcast Guest');
});

test('opening a new inquiry marks it in progress', function (): void {
    $inquiry = ContactInquiry::factory()->create(['status' => ContactInquiryStatus::New]);
    actingAsAdmin();

    livewire(ViewContactInquiry::class, ['record' => $inquiry->getRouteKey()])
        ->assertOk();

    expect($inquiry->refresh()
        ->status)->toBe(ContactInquiryStatus::InProgress);
});

test('opening a resolved inquiry keeps its status', function (): void {
    $inquiry = ContactInquiry::factory()->create(['status' => ContactInquiryStatus::Resolved]);
    actingAsAdmin();

    livewire(ViewContactInquiry::class, ['record' => $inquiry->getRouteKey()])
        ->assertOk();

    expect($inquiry->refresh()
        ->status)->toBe(ContactInquiryStatus::Resolved);
});

test('mark resolved header action resolves the inquiry', function (): void {
    $inquiry = ContactInquiry::factory()->create(['status' => ContactInquiryStatus::InProgress]);
    actingAsAdmin();

    livewire(ViewContactInquiry::class, ['record' => $inquiry->getRouteKey()])
        ->callAction('markResolved')
        ->assertActionHidden('markResolved');

    expect($inquiry->refresh()
        ->status)->toBe(ContactInquiryStatus::Resolved);
});

test('delivery stamps are shown read-only with their sent times in Eastern time', function (): void {
    $inquiry = ContactInquiry::factory()->create([
        'email_attempted_at' => Date::parse('2026-10-02 09:15:00'),
        'notification_sent_at' => Date::parse('2026-10-02 09:15:01'),
        'confirmation_sent_at' => null,
    ]);
    actingAsAdmin();

    livewire(ViewContactInquiry::class, ['record' => $inquiry->getRouteKey()])
        ->assertSee('Administrator notification sent')
        ->assertSee('Oct 2, 2026 5:15 AM')
        ->assertSee('Not sent');
});

test('administrators queue retries without sending during the request', function (): void {
    Bus::fake([SendContactInquiryEmails::class]);
    Event::fake([MessageSent::class]);
    $inquiry = ContactInquiry::factory()->create([
        'email_attempted_at' => Date::now(),
        'notification_sent_at' => Date::now(),
    ]);
    actingAsAdmin();

    livewire(ViewContactInquiry::class, ['record' => $inquiry->getRouteKey()])
        ->assertActionVisible('retryEmails')
        ->callAction('retryEmails')
        ->assertNotified('Email delivery queued');

    Bus::assertDispatched(SendContactInquiryEmails::class, fn (SendContactInquiryEmails $job): bool => $job->contactInquiryId === $inquiry->id);
    Event::assertNotDispatched(MessageSent::class);
    expect($inquiry->fresh()
        ?->confirmation_sent_at)->toBeNull();
});

test('the retry action appears only after an incomplete delivery attempt', function (bool $attempted, bool $delivered, bool $visible): void {
    $inquiry = ContactInquiry::factory()->create([
        'email_attempted_at' => $attempted ? Date::now() : null,
        'notification_sent_at' => $delivered ? Date::now() : null,
        'confirmation_sent_at' => $delivered ? Date::now() : null,
    ]);
    actingAsAdmin();

    $page = livewire(ViewContactInquiry::class, ['record' => $inquiry->getRouteKey()]);

    $visible
        ? $page->assertActionVisible('retryEmails')
        : $page->assertActionHidden('retryEmails');
})->with([
    'not attempted yet' => [false, false, false],
    'attempted and incomplete' => [true, false, true],
    'fully delivered' => [true, true, false],
]);

test('older contact inquiries do not invite retries with unknown delivery status', function (): void {
    $inquiry = ContactInquiry::factory()->create();
    actingAsAdmin();

    livewire(ViewContactInquiry::class, ['record' => $inquiry->getRouteKey()])
        ->assertSee('Not tracked')
        ->assertActionHidden('retryEmails');
});

test('non administrators cannot access contact email recovery', function (): void {
    $inquiry = ContactInquiry::factory()->create();
    actingAs(User::factory()->create());

    livewire(ViewContactInquiry::class, ['record' => $inquiry->getRouteKey()])
        ->assertForbidden();
});

test('tracked inquiries outside the retry window cannot queue admin retries', function (): void {
    Bus::fake([SendContactInquiryEmails::class]);
    $inquiry = ContactInquiry::factory()->create([
        'email_attempted_at' => Date::now()->subDay(),
        'created_at' => Date::now()->subDay(),
    ]);
    actingAsAdmin();

    livewire(ViewContactInquiry::class, ['record' => $inquiry->getRouteKey()])
        ->assertActionDisabled('retryEmails')
        ->callAction('retryEmails');

    Bus::assertNotDispatched(SendContactInquiryEmails::class);
});

test('reply action opens an encoded mail draft', function (): void {
    $inquiry = ContactInquiry::factory()->create(['email' => 'dale@example.com', 'type' => ContactType::General]);
    actingAsAdmin();

    livewire(ViewContactInquiry::class, ['record' => $inquiry->getRouteKey()])
        ->assertActionHasUrl('reply', 'mailto:dale@example.com?subject=Re%3A%20General%20Question');
});
