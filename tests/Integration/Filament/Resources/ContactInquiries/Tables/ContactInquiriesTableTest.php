<?php

use App\Filament\Resources\ContactInquiries\ContactInquiryResource;
use App\Filament\Resources\ContactInquiries\Pages\ListContactInquiries;
use App\Models\ContactInquiry;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
        ->assertTableColumnExists('type', fn (TextColumn $column): bool => $column->getColor('x') === ContactInquiryResource::TYPE_BADGE_COLOR)
        ->assertTableFilterExists('status')
        ->assertTableFilterExists('type')
        ->assertActionExists(TestAction::make('markResolved')->table($inquiry))
        ->assertActionExists(TestAction::make('reply')->table($inquiry))
        ->assertActionExists(TestAction::make('delete')->table($inquiry));
});
