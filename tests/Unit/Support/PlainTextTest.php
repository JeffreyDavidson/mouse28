<?php

use App\Support\PlainText;

covers(PlainText::class);

test('plain text from markdown keeps the words without markdown syntax or html', function (?string $markdown, string $expected): void {
    $text = PlainText::fromMarkdown($markdown);

    expect($text)->toBe($expected);
})->with([
    'markdown formatting' => ["## Park day\n\n**Arrive** early and _rest_ often.\n\n- Bring water", 'Park day Arrive early and rest often. Bring water'],
    'links keep their text' => ['Read the [guide](https://example.com/guide).', 'Read the guide.'],
    'html content' => ['<p>Plan a flexible arrival.</p>', 'Plan a flexible arrival.'],
    'entities decoded' => ['Rides & shows', 'Rides & shows'],
    'empty content' => [null, ''],
]);
