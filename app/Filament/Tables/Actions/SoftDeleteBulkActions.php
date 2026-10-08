<?php

declare(strict_types=1);

namespace App\Filament\Tables\Actions;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;

/**
 * The bulk actions for a soft-deleting resource's table: delete, force delete and restore.
 */
final class SoftDeleteBulkActions
{
    public static function make(): BulkActionGroup
    {
        return BulkActionGroup::make([
            DeleteBulkAction::make(),
            ForceDeleteBulkAction::make(),
            RestoreBulkAction::make(),
        ]);
    }
}
