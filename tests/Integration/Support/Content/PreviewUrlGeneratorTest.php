<?php

use App\Models\Episode;
use App\Models\Guide;
use App\Models\NewsletterIssue;
use App\Models\Post;
use App\Support\Content\PreviewUrlGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;

covers(PreviewUrlGenerator::class);

pest()->use(RefreshDatabase::class);

test('preview links are signed slug addresses that expire after the configured hours', function (Post|Guide|Episode|NewsletterIssue $content, string $path): void {
    Date::setTestNow('2026-09-27 12:00:00');
    config()->set('mouse28.preview_link_hours', 6);

    $url = app(PreviewUrlGenerator::class)->for($content);
    parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

    expect(parse_url($url, PHP_URL_PATH))->toBe("/preview/{$path}/{$content->slug}")
        ->and((int) $query['expires'])
        ->toBe(Date::now()
            ->addHours(6)
            ->getTimestamp())
        ->and(Request::create($url)->hasValidSignature())
        ->toBeTrue();
})->with([
    'post' => [fn (): Post => Post::factory()
        ->draft()
        ->create(), 'posts'],
    'guide' => [fn (): Guide => Guide::factory()
        ->draft()
        ->create(), 'guides'],
    'episode' => [fn (): Episode => Episode::factory()
        ->draft()
        ->create(), 'episodes'],
    'newsletter issue' => [fn (): NewsletterIssue => NewsletterIssue::factory()
        ->draft()
        ->create(), 'newsletter'],
]);
