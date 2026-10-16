<?php

use App\Models\Subscriber;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JeffreyDavidson\CreatorKit\Filament\Resources\Subscribers\Pages\ListSubscribers;
use JeffreyDavidson\CreatorKit\Filament\Resources\Subscribers\SubscriberResource;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function (): void {
    actingAs(User::factory()
        ->admin()
        ->create());
});

test('administrators see every reader with a readable status once the filter is cleared', function (): void {
    // Arrange
    $active = Subscriber::factory()->create();
    $pending = Subscriber::factory()
        ->pending()
        ->create();
    $unsubscribed = Subscriber::factory()
        ->unsubscribed()
        ->create();
    $suppressed = Subscriber::factory()
        ->suppressed()
        ->create();

    // Act
    $response = get(SubscriberResource::getUrl());
    $page = livewire(ListSubscribers::class)->filterTable('status', null);

    // Assert
    $response->assertOk()
        ->assertSee('Subscribers');
    $page->assertCanSeeTableRecords([$active, $pending, $unsubscribed, $suppressed])
        ->assertSee(['Active', 'Pending confirmation', 'Unsubscribed', 'Suppressed']);
});

test('the list opens on active readers', function (): void {
    // Arrange
    $active = Subscriber::factory()->create();
    $others = [
        Subscriber::factory()
            ->pending()
            ->create(),
        Subscriber::factory()
            ->unsubscribed()
            ->create(),
        Subscriber::factory()
            ->suppressed()
            ->create(),
    ];

    // Act
    $page = livewire(ListSubscribers::class);

    // Assert
    $page->assertCanSeeTableRecords([$active])
        ->assertCanNotSeeTableRecords($others);
});

test('the status filter narrows the list', function (string $status, string $visible): void {
    $readers = [
        'active' => Subscriber::factory()->create(),
        'pending' => Subscriber::factory()
            ->pending()
            ->create(),
        'unsubscribed' => Subscriber::factory()
            ->unsubscribed()
            ->create(),
        'suppressed' => Subscriber::factory()
            ->suppressed()
            ->create(),
    ];

    livewire(ListSubscribers::class)
        ->filterTable('status', $status)
        ->assertCanSeeTableRecords([$readers[$visible]])
        ->assertCountTableRecords(1);
})->with([
    'active' => ['active', 'active'],
    'pending' => ['pending', 'pending'],
    'unsubscribed' => ['unsubscribed', 'unsubscribed'],
    'suppressed' => ['suppressed', 'suppressed'],
]);

test('readers can be found by email', function (): void {
    $match = Subscriber::factory()->create(['email' => 'match@example.test']);
    $other = Subscriber::factory()->create(['email' => 'other@example.test']);

    livewire(ListSubscribers::class)
        ->searchTable('match@')
        ->assertCanSeeTableRecords([$match])
        ->assertCanNotSeeTableRecords([$other]);
});

test('readers arrive only through the public form', function (): void {
    expect(SubscriberResource::canCreate())->toBeFalse()
        ->and(array_keys(SubscriberResource::getPages()))
        ->toBe(['index']);
});

test('administrators delete readers in bulk, with no delete button on each row', function (): void {
    // Arrange
    $readers = Subscriber::factory()
        ->count(2)
        ->create();

    // Act
    $page = livewire(ListSubscribers::class)
        ->assertActionDoesNotExist(TestAction::make(DeleteAction::class)->table($readers->first()))
        ->selectTableRecords($readers)
        ->callAction(TestAction::make(DeleteBulkAction::class)
            ->table()
            ->bulk());

    // Assert
    $page->assertHasNoErrors();
    expect(Subscriber::query()->count())->toBe(0);
});

test('suppressed readers cannot be deleted so the do-not-email list survives', function (): void {
    $suppressed = Subscriber::factory()
        ->suppressed()
        ->create();

    livewire(ListSubscribers::class)
        ->filterTable('status', 'suppressed')
        ->assertCanSeeTableRecords([$suppressed])
        ->selectTableRecords([$suppressed])
        ->callAction(TestAction::make(DeleteBulkAction::class)
            ->table()
            ->bulk());

    $this->assertModelExists($suppressed);
});

test('other users cannot reach the subscriber list', function (): void {
    actingAs(User::factory()->create());

    get(SubscriberResource::getUrl())->assertForbidden();
});
