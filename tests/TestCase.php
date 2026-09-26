<?php

namespace Tests;

use Dom\Element;
use Dom\HTMLDocument;
use Dom\XPath;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Http;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\Response;
use UnexpectedValueException;

abstract class TestCase extends BaseTestCase
{
    public function createApplication(): Application
    {
        $app = parent::createApplication();

        if (! $app->environment('testing') || $app->configurationIsCached()
            || config('database.default') !== 'sqlite'
            || config('database.connections.sqlite.database') !== ':memory:'
            || filled(config('database.connections.sqlite.url'))) {
            throw new UnexpectedValueException('Tests require uncached testing configuration and an isolated database.');
        }

        // Opt-in compatibility runs use only a disposable loopback database and test-only credentials.
        if (getenv('MOUSE28_TEST_MYSQL') === '1') {
            config()->set([
                'database.default' => 'mysql',
                'database.connections.mysql.url' => null,
                'database.connections.mysql.host' => '127.0.0.1',
                'database.connections.mysql.port' => getenv('MOUSE28_TEST_MYSQL_PORT') ?: '3306',
                'database.connections.mysql.database' => 'mouse28_test',
                'database.connections.mysql.username' => 'mouse28_test',
                'database.connections.mysql.password' => 'mouse28_test',
                'database.connections.mysql.unix_socket' => '',
            ]);
        }

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();

        $this->withoutVite();
    }

    /**
     * Returns the links that open a new tab without announcing it in their accessible text.
     *
     * @param  TestResponse<Response>  $response
     * @return list<string>
     */
    protected function unannouncedNewTabLinks(TestResponse $response): array
    {
        $document = HTMLDocument::createFromString($this->responseContent($response), LIBXML_NOERROR);
        $unannounced = [];

        foreach (new XPath($document)->query('//*[local-name()="a"][@target="_blank"]') as $link) {
            if (! $link instanceof Element) {
                continue;
            }

            $accessibleText = "{$link->getAttribute('aria-label')} {$link->textContent}";

            if (! str_contains(strtolower($accessibleText), 'new tab')) {
                $unannounced[] = (string) $link->getAttribute('href');
            }
        }

        return $unannounced;
    }

    /**
     * @param  TestResponse<Response>  $response
     */
    protected function responseContent(TestResponse $response): string
    {
        $content = $response->getContent();

        if ($content === false) {
            throw new UnexpectedValueException('The response body could not be read.');
        }

        return $content;
    }

    /**
     * @param  TestResponse<Response>  $response
     * @return array<string, mixed>
     */
    protected function structuredData(TestResponse $response): array
    {
        $content = $this->responseContent($response);

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
