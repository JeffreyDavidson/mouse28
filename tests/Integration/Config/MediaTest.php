<?php

test('mouse28 generates responsive variants at 480, 640, 768 and 1280 pixels', function (): void {
    expect(config('media.responsive_widths'))->toBe([480, 640, 768, 1280]);
});
