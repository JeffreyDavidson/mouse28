<?php

test('test configuration disables sentry and personal information collection', function (): void {
    expect(config('sentry.dsn'))->toBeEmpty()
        ->and(config('sentry.send_default_pii'))->toBeFalse();
});
