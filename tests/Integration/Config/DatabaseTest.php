<?php

test('the sqlite connection uses the settings production sqlite needs', function (): void {
    expect(config('database.connections.sqlite'))->toMatchArray([
        'busy_timeout' => 5000,
        'journal_mode' => 'WAL',
        'synchronous' => 'NORMAL',
        'transaction_mode' => 'IMMEDIATE',
    ]);
});
