<?php

test('quick draft submit control declares the project touch target size', function (): void {
    $view = file_get_contents(dirname(__DIR__, 5).'/resources/views/filament/widgets/quick-draft.blade.php');

    expect($view)
        ->toContain('class="min-h-12"')
        ->not->toContain('class="min-h-10"');
});
