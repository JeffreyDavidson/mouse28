<?php

use App\Actions\SendContactMessage;
use App\Data\ContactMessageData;
use App\Enums\ContactInquiryStatus;
use App\Enums\ContactType;
use App\Jobs\SendContactInquiryEmails;
use App\Models\ContactInquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\assertDatabaseCount;

pest()->use(RefreshDatabase::class);

covers(SendContactMessage::class);

function contactMessageData(): ContactMessageData
{
    return new ContactMessageData(
        name: 'Dale Cooper',
        email: 'dale@example.test',
        type: ContactType::Accessibility,
        message: 'Please help with this park question.',
    );
}

test('action stores a contact inquiry and queues one email job without contact details', function (): void {
    app(SendContactMessage::class)->handle(contactMessageData());

    $inquiry = ContactInquiry::query()->sole();
    $payload = DB::table('jobs')
        ->sole()
        ->payload;
    $encryptedCommand = data_get(json_decode(is_string($payload) ? $payload : '', true, flags: JSON_THROW_ON_ERROR), 'data.command');

    if (! is_string($encryptedCommand)) {
        throw new UnexpectedValueException('Expected an encrypted queued command.');
    }

    $command = Crypt::decrypt($encryptedCommand);

    expect($inquiry)
        ->name->toBe('Dale Cooper')
        ->email->toBe('dale@example.test')
        ->type->toBe(ContactType::Accessibility)
        ->message->toBe('Please help with this park question.')
        ->status->toBe(ContactInquiryStatus::New)
        ->and($command)
        ->toBeString()
        ->toContain(SendContactInquiryEmails::class)
        ->toContain("i:{$inquiry->id};")
        ->not->toContain('Dale Cooper', 'dale@example.test', 'Please help with this park question.');
});

test('action rolls back the inquiry when its email job cannot be queued and allows a clean retry', function (): void {
    $inserts = 0;
    DB::connection()->beforeExecuting(function (string $query) use (&$inserts): void {
        if (str_contains($query, 'insert into "jobs"') && ++$inserts === 1) {
            throw new RuntimeException('Synthetic queue failure.');
        }
    });

    expect(fn () => app(SendContactMessage::class)->handle(contactMessageData()))
        ->toThrow(RuntimeException::class, 'Synthetic queue failure.');
    assertDatabaseCount('contact_inquiries', 0);
    assertDatabaseCount('jobs', 0);

    app(SendContactMessage::class)->handle(contactMessageData());

    assertDatabaseCount('contact_inquiries', 1);
    assertDatabaseCount('jobs', 1);
});

test('action refuses a queue database separate from the application before saving', function (): void {
    config()->set([
        'queue.connections.database.connection' => 'separate',
        'database.connections.separate' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''],
    ]);

    expect(fn () => app(SendContactMessage::class)->handle(contactMessageData()))
        ->toThrow(LogicException::class, 'Contact notifications must share the application database.');

    assertDatabaseCount('contact_inquiries', 0);
});
