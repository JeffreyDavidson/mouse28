<?php

use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Filament\Resources\ContactMessages\Pages\ViewContactMessage;
use App\Jobs\SendContactMessageEmails;
use App\Models\ContactMessage;
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

test('contact topics and free text retain readable admin labels', function (string $subject, string $label): void {
    $message = ContactMessage::query()->create([
        'name' => 'Dale Cooper',
        'email' => 'dale@example.com',
        'subject' => $subject,
        'message' => 'A question about the parks.',
    ]);
    actingAs(User::factory()->admin()->create());

    get(ContactMessageResource::getUrl('view', ['record' => $message]))
        ->assertOk()
        ->assertSee($label);
})->with([
    'recognized topic' => ['guest', 'Podcast Guest'],
    'free text' => ['A custom question', 'A custom question'],
]);

test('administrators queue retries without sending during the request', function (): void {
    Bus::fake([SendContactMessageEmails::class]);
    Event::fake([MessageSent::class]);
    $message = ContactMessage::query()->create([
        'name' => 'Dale Cooper', 'email' => 'dale@example.com',
        'subject' => 'general', 'message' => 'A park question.',
    ]);
    $message->email_attempted_at = Date::now();
    $message->notification_sent_at = Date::now();
    $message->save();
    actingAs(User::factory()->admin()->create());

    livewire(ViewContactMessage::class, ['record' => $message->getRouteKey()])
        ->assertActionVisible('retryEmails')
        ->callAction('retryEmails')
        ->assertNotified('Email delivery queued');

    Bus::assertDispatched(SendContactMessageEmails::class, fn (SendContactMessageEmails $job): bool => $job->contactMessageId === $message->id);
    Event::assertNotDispatched(MessageSent::class);
    expect($message->fresh()?->confirmation_sent_at)->toBeNull();
});

test('older contact messages do not invite retries with unknown delivery status', function (): void {
    $message = ContactMessage::query()->create([
        'name' => 'Dale Cooper', 'email' => 'dale@example.com',
        'subject' => 'general', 'message' => 'A park question.',
    ]);
    actingAs(User::factory()->admin()->create());

    livewire(ViewContactMessage::class, ['record' => $message->getRouteKey()])
        ->assertSee('Not tracked')
        ->assertActionHidden('retryEmails');
});

test('non administrators cannot access contact email recovery', function (): void {
    $message = ContactMessage::query()->create([
        'name' => 'Dale Cooper', 'email' => 'dale@example.com',
        'subject' => 'general', 'message' => 'A park question.',
    ]);
    actingAs(User::factory()->create());

    livewire(ViewContactMessage::class, ['record' => $message->getRouteKey()])
        ->assertForbidden();
});

test('tracked messages outside the retry window cannot queue admin retries', function (): void {
    Bus::fake([SendContactMessageEmails::class]);
    $message = ContactMessage::query()->create([
        'name' => 'Reader', 'email' => 'reader@example.com',
        'subject' => 'general', 'message' => 'A question.',
    ]);
    $message->email_attempted_at = Date::now()->subDay();
    $message->created_at = Date::now()->subDay();
    $message->save();
    actingAs(User::factory()->admin()->create());

    livewire(ViewContactMessage::class, ['record' => $message->getRouteKey()])
        ->assertActionDisabled('retryEmails')
        ->callAction('retryEmails');

    Bus::assertNotDispatched(SendContactMessageEmails::class);
});
