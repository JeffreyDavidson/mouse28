<?php

use App\Filament\Resources\Subscribers\Pages\ListSubscribers;
use App\Filament\Resources\Subscribers\SubscriberResource;
use App\Models\Subscriber;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function (): void {
    actingAs(User::factory()->admin()->create());
});

test('administrators see every reader with a readable status', function (): void {
    $active = Subscriber::factory()->create();
    $pending = Subscriber::factory()->pending()->create();
    $unsubscribed = Subscriber::factory()->unsubscribed()->create();
    $suppressed = Subscriber::factory()->suppressed()->create();

    get(SubscriberResource::getUrl())
        ->assertOk()
        ->assertSee('Newsletter Subscribers')
        ->assertSee([$active->email, $pending->email, $unsubscribed->email, $suppressed->email])
        ->assertSee(['Active', 'Pending confirmation', 'Unsubscribed', 'Suppressed']);
});

test('the status filter narrows the list', function (string $status, string $visible): void {
    $readers = [
        'active' => Subscriber::factory()->create(),
        'pending' => Subscriber::factory()->pending()->create(),
        'unsubscribed' => Subscriber::factory()->unsubscribed()->create(),
        'suppressed' => Subscriber::factory()->suppressed()->create(),
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

test('the header counts readers by status', function (): void {
    Subscriber::factory()->count(2)->create();
    Subscriber::factory()->pending()->create();
    Subscriber::factory()->unsubscribed()->create();
    Subscriber::factory()->suppressed()->create();

    $header = livewire(ListSubscribers::class)->instance()->getHeader();

    expect($header?->getData())->toMatchArray(['active' => 2, 'pending' => 1, 'unsubscribed' => 1, 'suppressed' => 1]);
});

test('readers arrive only through the public form', function (): void {
    expect(SubscriberResource::canCreate())->toBeFalse()
        ->and(array_keys(SubscriberResource::getPages()))->toBe(['index']);
});

test('administrators can delete one reader or several', function (): void {
    $single = Subscriber::factory()->create();
    $bulk = Subscriber::factory()->count(2)->create();

    livewire(ListSubscribers::class)
        ->callAction(TestAction::make(DeleteAction::class)->table($single));

    expect(Subscriber::query()->whereKey($single->id)->exists())->toBeFalse();

    livewire(ListSubscribers::class)
        ->selectTableRecords($bulk)
        ->callAction(TestAction::make(DeleteBulkAction::class)->table()->bulk());

    expect(Subscriber::query()->count())->toBe(0);
});

test('suppressed readers cannot be deleted so the do-not-email list survives', function (): void {
    $suppressed = Subscriber::factory()
        ->suppressed()
        ->create();

    livewire(ListSubscribers::class)
        ->filterTable('status', 'suppressed')
        ->assertCanSeeTableRecords([$suppressed])
        ->assertActionHidden(TestAction::make(DeleteAction::class)->table($suppressed))
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
