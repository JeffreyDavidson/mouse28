<?php

use App\Enums\ContactInquiryStatus;
use App\Enums\ContactType;
use App\Filament\Resources\ContactInquiries\ContactInquiryResource;
use App\Filament\Resources\ContactInquiries\Pages\ListContactInquiries;
use App\Models\ContactInquiry;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

test('authenticated user can render the resource listing', function (): void {
    actingAs(User::factory()->admin()->create());

    get(ContactInquiryResource::getUrl())
        ->assertOk()
        ->assertSee('Contact Inquiries');
});

test('the listing decrypts the sender and shows readable type and status labels', function (): void {
    ContactInquiry::factory()->create([
        'name' => 'Dale Cooper',
        'email' => 'dale@example.com',
        'type' => ContactType::Guest,
        'status' => ContactInquiryStatus::InProgress,
    ]);
    actingAs(User::factory()->admin()->create());

    get(ContactInquiryResource::getUrl())
        ->assertOk()
        ->assertSee('Dale Cooper')
        ->assertSee('dale@example.com')
        ->assertSee('Podcast Guest')
        ->assertSee('In progress');
});

test('the listing header counts total and new inquiries', function (): void {
    ContactInquiry::factory()->create(['status' => ContactInquiryStatus::New]);
    ContactInquiry::factory()->create(['status' => ContactInquiryStatus::Resolved]);
    actingAs(User::factory()->admin()->create());

    $content = (string) get(ContactInquiryResource::getUrl())
        ->assertOk()
        ->getContent();

    expect($content)->toMatch('/>2<\/span>\s*<span[^>]*>Total<\/span>/')
        ->toMatch('/>1<\/span>\s*<span[^>]*>New<\/span>/');
});

test('renders contact inquiry previews with a two-line clamp', function (): void {
    ContactInquiry::factory()->create([
        'message' => 'A family wants to understand the attraction before visiting. More detail helps them prepare for the experience.',
    ]);
    actingAs(User::factory()->admin()->create());

    get(ContactInquiryResource::getUrl())
        ->assertOk()
        ->assertSeeHtml('A family wants to understand the attraction')
        ->assertSeeHtml('--line-clamp: 2');
});

test('the listing filters inquiries by status', function (): void {
    $new = ContactInquiry::factory()->create(['status' => ContactInquiryStatus::New]);
    $resolved = ContactInquiry::factory()->create(['status' => ContactInquiryStatus::Resolved]);
    actingAs(User::factory()->admin()->create());

    livewire(ListContactInquiries::class)
        ->filterTable('status', ContactInquiryStatus::New->value)
        ->assertCanSeeTableRecords([$new])
        ->assertCanNotSeeTableRecords([$resolved]);
});

test('the listing searches the stored contact type', function (): void {
    $guest = ContactInquiry::factory()->create(['type' => ContactType::Guest]);
    $general = ContactInquiry::factory()->create(['type' => ContactType::General]);
    actingAs(User::factory()->admin()->create());

    livewire(ListContactInquiries::class)
        ->searchTable('guest')
        ->assertCanSeeTableRecords([$guest])
        ->assertCanNotSeeTableRecords([$general]);
});

test('reply action opens an encoded mail draft', function (): void {
    $inquiry = ContactInquiry::factory()->create(['email' => 'dale@example.com', 'type' => ContactType::General]);
    actingAs(User::factory()->admin()->create());

    livewire(ListContactInquiries::class)
        ->assertActionHasUrl(TestAction::make('reply')->table($inquiry), 'mailto:dale@example.com?subject=Re%3A%20General%20Question');
});

test('mark resolved action resolves an open inquiry', function (): void {
    $inquiry = ContactInquiry::factory()->create(['status' => ContactInquiryStatus::InProgress]);
    actingAs(User::factory()->admin()->create());

    livewire(ListContactInquiries::class)
        ->callAction(TestAction::make('markResolved')->table($inquiry));

    expect($inquiry->refresh()->status)->toBe(ContactInquiryStatus::Resolved);
});

test('mark resolved action is hidden for resolved inquiries', function (): void {
    $inquiry = ContactInquiry::factory()->create(['status' => ContactInquiryStatus::Resolved]);
    actingAs(User::factory()->admin()->create());

    livewire(ListContactInquiries::class)
        ->assertActionHidden(TestAction::make('markResolved')->table($inquiry));
});

test('mark resolved action disappears when admin access is revoked', function (): void {
    $admin = User::factory()->admin()->create();
    actingAs($admin);
    $inquiry = ContactInquiry::factory()->create(['status' => ContactInquiryStatus::New]);
    $page = livewire(ListContactInquiries::class);

    $admin->is_admin = false;

    $page->assertActionHidden(TestAction::make('markResolved')->table($inquiry));
});
