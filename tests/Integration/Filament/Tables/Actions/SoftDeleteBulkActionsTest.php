<?php

use App\Filament\Tables\Actions\SoftDeleteBulkActions;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;

covers(SoftDeleteBulkActions::class);

test('the soft delete bulk actions group delete, force delete and restore in order', function (): void {
    $group = SoftDeleteBulkActions::make();

    expect(array_map(get_class(...), $group->getActions()))
        ->toBe([DeleteBulkAction::class, ForceDeleteBulkAction::class, RestoreBulkAction::class]);
});
