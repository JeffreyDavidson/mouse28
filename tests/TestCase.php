<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        $this->mockConsoleOutput = false;

        parent::setUp();

        $this->withoutVite();
    }
}
