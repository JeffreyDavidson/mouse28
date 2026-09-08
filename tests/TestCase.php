<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Http;
use Illuminate\Testing\TestResponse;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        $this->mockConsoleOutput = false;

        parent::setUp();

        Http::preventStrayRequests();

        $this->withoutVite();
    }

    /** @return array<string, mixed> */
    protected function structuredData(TestResponse $response): array
    {
        $matched = preg_match(
            '/<script type="application\/ld\+json">(.*?)<\/script>/s',
            $response->getContent(),
            $matches,
        );

        expect($matched)->toBe(1, 'The response did not contain JSON-LD structured data.');

        return json_decode($matches[1], true, flags: JSON_THROW_ON_ERROR);
    }
}
