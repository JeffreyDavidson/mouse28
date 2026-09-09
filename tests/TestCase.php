<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Http;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\Response;
use UnexpectedValueException;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        $this->mockConsoleOutput = false;

        parent::setUp();

        Http::preventStrayRequests();

        $this->withoutVite();
    }

    /**
     * @param  TestResponse<Response>  $response
     * @return array<string, mixed>
     */
    protected function structuredData(TestResponse $response): array
    {
        $content = $response->getContent();

        if ($content === false) {
            throw new UnexpectedValueException('The response body could not be read.');
        }

        $matched = preg_match(
            '/<script type="application\/ld\+json">(.*?)<\/script>/s',
            $content,
            $matches,
        );

        if ($matched !== 1 || ! isset($matches[1]) || ! is_string($matches[1])) {
            throw new UnexpectedValueException('The response did not contain JSON-LD structured data.');
        }

        $data = json_decode($matches[1], true, flags: JSON_THROW_ON_ERROR);

        if (! is_array($data)) {
            throw new UnexpectedValueException('The JSON-LD structured data was not an object.');
        }

        $structuredData = [];

        foreach ($data as $key => $value) {
            if (! is_string($key)) {
                throw new UnexpectedValueException('The JSON-LD structured data must be an object.');
            }

            $structuredData[$key] = $value;
        }

        return $structuredData;
    }
}
