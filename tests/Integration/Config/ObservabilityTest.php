<?php

test('debug tooling is limited to its intended environments', function (): void {
    expect(config('newdebugbar.environments'))->toBe(['local'])
        ->and((bool) config('telescope.enabled'))->toBeFalse();
});
