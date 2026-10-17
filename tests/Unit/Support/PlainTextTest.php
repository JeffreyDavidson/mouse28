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

test('keeping a dash with the preceding word joins only spaced dashes with a non-breaking space', function (string $title, string $expected): void {
    // Arrange
    $original = $title;

    // Act
    $text = PlainText::keepDashWithPrecedingWord($original);

    // Assert
    expect($text)->toBe($expected);
})->with([
    'em dash' => ['Planning a Calm Park Day — What Actually Helps', "Planning a Calm Park Day\u{00A0}— What Actually Helps"],
    'en dash' => ['Season one – the finale', "Season one\u{00A0}– the finale"],
    'unspaced dash' => ['Park-day notes—quick', 'Park-day notes—quick'],
    'no dash' => ['Your DAS Questions, Answered', 'Your DAS Questions, Answered'],
]);
