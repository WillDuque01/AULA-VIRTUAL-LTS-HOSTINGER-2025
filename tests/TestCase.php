<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\URL;

abstract class TestCase extends BaseTestCase
{
    protected string $testingLocale = 'es';

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.locale' => $this->testingLocale]);
        app()->setLocale($this->testingLocale);
        \Illuminate\Support\Facades\URL::defaults(['locale' => $this->testingLocale]);
    }

    protected function localized(string $path = ''): string
    {
        $path = '/'.ltrim($path, '/');

        if ($path === '/') {
            return '/'.$this->testingLocale;
        }

        return '/'.$this->testingLocale.$path;
    }
}
