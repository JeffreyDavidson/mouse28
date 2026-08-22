<?php

test('sentry is disabled by default and does not collect personal information', function (): void {
    expect(config('sentry.dsn'))->toBeNull()
        ->and(config('sentry.send_default_pii'))->toBeFalse();
});
