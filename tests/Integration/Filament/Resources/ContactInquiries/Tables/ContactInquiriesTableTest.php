<?php

use App\Models\ContactInquiry;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JeffreyDavidson\CreatorKit\Filament\Resources\ContactInquiries\Pages\ListContactInquiries;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

test('the inquiries table lists its columns, filters and record actions', function (): void {
    $inquiry = ContactInquiry::factory()->create();
    actingAs(User::factory()
        ->admin()
        ->create());

    livewire(ListContactInquiries::class)
        ->assertCanSeeTableRecords([$inquiry])
        ->assertTableColumnExists('type', fn (TextColumn $column): bool => $column->isBadge())
        ->assertTableFilterExists('status')
        ->assertTableFilterExists('type')
        ->assertActionExists(TestAction::make('markResolved')->table($inquiry))
        ->assertActionExists(TestAction::make('reply')->table($inquiry))
        ->assertActionExists(TestAction::make('edit')->table($inquiry))
        ->assertActionDoesNotExist(TestAction::make('delete')->table($inquiry));
});
