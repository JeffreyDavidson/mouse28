<?php

use App\Enums\ContactInquiryStatus;
use App\Enums\ContactType;
use App\Models\ContactInquiry;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

pest()->use(RefreshDatabase::class);

/**
 * Inserts rows shaped like production's contact_messages: ID gaps from deletions,
 * recognized topics and older free-text subjects, read and unread flags, and
 * delivery stamps only on messages sent after tracking began.
 */
function seedLegacyContactMessages(): void
{
    DB::table('contact_messages')->insert([
        [
            'id' => 3, 'name' => 'Pre-tracking Reader', 'email' => 'early@example.test',
            'subject' => 'Need help with Mouse28', 'message' => 'An older free-text question.',
            'is_read' => true, 'created_at' => '2026-03-05 10:00:00', 'updated_at' => '2026-03-06 09:00:00',
            'email_attempted_at' => null, 'notification_sent_at' => null, 'confirmation_sent_at' => null,
        ],
        [
            'id' => 7, 'name' => 'Guest Hopeful', 'email' => 'guest@example.test',
            'subject' => 'guest', 'message' => 'I would love to join the podcast.',
            'is_read' => false, 'created_at' => '2026-09-20 14:30:00', 'updated_at' => '2026-09-20 14:30:00',
            'email_attempted_at' => '2026-09-20 14:30:05', 'notification_sent_at' => '2026-09-20 14:30:06', 'confirmation_sent_at' => '2026-09-20 14:30:07',
        ],
        [
            'id' => 42, 'name' => 'Ünïcode Fan', 'email' => 'fan@example.test',
            'subject' => 'other', 'message' => "Line one.\nLine two with emoji 🐭.",
            'is_read' => true, 'created_at' => '2026-10-01 08:15:00', 'updated_at' => '2026-10-01 09:00:00',
            'email_attempted_at' => '2026-10-01 08:15:02', 'notification_sent_at' => '2026-10-01 08:15:03', 'confirmation_sent_at' => null,
        ],
    ]);
}

function runContactCopyMigration(): void
{
    $migration = require database_path('migrations/2026_10_02_164233_copy_contact_messages_to_contact_inquiries.php');

    if (! $migration instanceof Migration || ! method_exists($migration, 'up')) {
        throw new UnexpectedValueException('The contact copy migration could not be loaded.');
    }

    $migration->up();
}

test('copying contact messages keeps every row with its ID, timestamps and delivery stamps', function (): void {
    seedLegacyContactMessages();

    runContactCopyMigration();

    expect(DB::table('contact_inquiries')
        ->orderBy('id')
        ->get(['id', 'created_at', 'updated_at', 'email_attempted_at', 'notification_sent_at', 'confirmation_sent_at'])
        ->map(fn (object $row): array => (array) $row)
        ->all())
        ->toBe(DB::table('contact_messages')
            ->orderBy('id')
            ->get(['id', 'created_at', 'updated_at', 'email_attempted_at', 'notification_sent_at', 'confirmation_sent_at'])
            ->map(fn (object $row): array => (array) $row)
            ->all());
});

test('copying contact messages encrypts personal fields at rest', function (): void {
    seedLegacyContactMessages();

    runContactCopyMigration();

    $raw = DB::table('contact_inquiries')
        ->where('id', 7)
        ->first(['name', 'email', 'message']);
    $inquiry = ContactInquiry::query()->findOrFail(7);

    expect((array) $raw)->each->not->toBeIn(['Guest Hopeful', 'guest@example.test', 'I would love to join the podcast.'])
        ->and($inquiry->name)
        ->toBe('Guest Hopeful')
        ->and($inquiry->email)
        ->toBe('guest@example.test')
        ->and($inquiry->message)
        ->toBe('I would love to join the podcast.');
});

test('copying contact messages maps known topics to their type and keeps the message', function (): void {
    seedLegacyContactMessages();

    runContactCopyMigration();

    expect(ContactInquiry::query()->findOrFail(7))
        ->type->toBe(ContactType::Guest)
        ->message->toBe('I would love to join the podcast.')
        ->and(ContactInquiry::query()->findOrFail(42))
        ->type->toBe(ContactType::Other)
        ->message->toBe("Line one.\nLine two with emoji 🐭.");
});

test('copying contact messages files free-text subjects under other with the subject prepended', function (): void {
    seedLegacyContactMessages();

    runContactCopyMigration();

    expect(ContactInquiry::query()->findOrFail(3))
        ->type->toBe(ContactType::Other)
        ->message->toBe("Subject: Need help with Mouse28\n\nAn older free-text question.");
});

test('copying contact messages maps unread to new and read to in progress', function (): void {
    seedLegacyContactMessages();

    runContactCopyMigration();

    expect(ContactInquiry::query()
        ->orderBy('id')
        ->pluck('status', 'id')
        ->all())->toBe([
            3 => ContactInquiryStatus::InProgress,
            7 => ContactInquiryStatus::New,
            42 => ContactInquiryStatus::InProgress,
        ]);
});

test('copying contact messages keeps the legacy table and continues new IDs after the copied ones', function (): void {
    seedLegacyContactMessages();

    runContactCopyMigration();
    $next = ContactInquiry::factory()->create();

    expect(DB::table('contact_messages')->count())->toBe(3)
        ->and($next->id)
        ->toBeGreaterThan(42);
});
