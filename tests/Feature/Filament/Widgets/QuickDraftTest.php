<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('quick draft submit control meets the project touch target size', function (): void {
    $view = file_get_contents(resource_path('views/filament/widgets/quick-draft.blade.php'));

    expect($view)
        ->toContain('inline-flex min-h-12 items-center gap-1.5 rounded-xl')
        ->not->toContain('inline-flex min-h-10 items-center gap-1.5 rounded-xl');
});
