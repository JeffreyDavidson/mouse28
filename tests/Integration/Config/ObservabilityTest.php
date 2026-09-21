<?php

test('debug tooling is limited to its intended environments', function (): void {
    expect(config('newdebugbar.environments'))->toBe(['local'])
        ->and(config('telescope.enabled'))->toBeFalsy();
});
