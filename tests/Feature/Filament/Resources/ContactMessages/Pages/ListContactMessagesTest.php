<?php

use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Filament\Resources\ContactMessages\Pages\ListContactMessages;
use App\Models\ContactMessage;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

test('authenticated user can render the resource listing', function (): void {
    actingAs(User::factory()->admin()->create());

    get(ContactMessageResource::getUrl())
        ->assertOk()
        ->assertSee('Contact Messages');
});

test('contact topics and free text retain readable admin labels', function (string $subject, string $label): void {
    ContactMessage::query()->create([
        'name' => 'Dale Cooper',
        'email' => 'dale@example.com',
        'subject' => $subject,
        'message' => 'A question about the parks.',
    ]);
    actingAs(User::factory()->admin()->create());

    get(ContactMessageResource::getUrl())
        ->assertOk()
        ->assertSee($label);
})->with([
    'recognized topic' => ['guest', 'Podcast Guest'],
    'free text' => ['A custom question', 'A custom question'],
]);

test('renders contact message previews with a two-line clamp', function (): void {
    ContactMessage::query()->create([
        'name' => 'Alex Example',
        'email' => 'alex@example.com',
        'subject' => 'A custom question',
        'message' => 'A family wants to understand the attraction before visiting. More detail helps them prepare for the experience.',
    ]);
    actingAs(User::factory()->admin()->create());

    get(ContactMessageResource::getUrl())
        ->assertOk()
        ->assertSeeHtml('A family wants to understand the attraction')
        ->assertSeeHtml('--line-clamp: 2');
});

test('reply action opens an encoded mail draft', function (): void {
    $message = ContactMessage::query()->create([
        'name' => 'Dale Cooper',
        'email' => 'dale@example.com',
        'subject' => 'general',
        'message' => 'A park question.',
    ]);
    actingAs(User::factory()->admin()->create());

    livewire(ListContactMessages::class)
        ->assertActionHasUrl(TestAction::make('reply')->table($message), 'mailto:dale@example.com?subject=Re%3A%20General%20Question');
});

test('mark read action requires permission to update the message', function (): void {
    actingAs(User::factory()->admin()->create());
    Gate::before(fn (User $user, string $ability): ?bool => $ability === 'update' ? false : null);
    $message = ContactMessage::query()->create([
        'name' => 'Dale Cooper',
        'email' => 'dale@example.com',
        'subject' => 'general',
        'message' => 'A park question.',
    ]);

    livewire(ListContactMessages::class)
        ->assertActionHidden(TestAction::make('markRead')->table($message));
});
