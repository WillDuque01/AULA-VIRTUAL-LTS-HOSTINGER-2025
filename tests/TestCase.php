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

        $databasePath = database_path('testing.sqlite');

        if (! file_exists($databasePath)) {
            touch($databasePath);
        }

        config([
            'app.locale' => $this->testingLocale,
            'session.driver' => 'file',
            'queue.default' => 'sync',
            'cache.default' => 'array',
            'mail.default' => 'array',
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => $databasePath,
            'hashing.driver' => 'bcrypt',
            'hashing.bcrypt.verify' => false,
            'hashing.argon.verify' => false,
            'telescope.enabled' => false,
        ]);

        app()->setLocale($this->testingLocale);
        URL::defaults(['locale' => $this->testingLocale]);

        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);
    }

    public function call($method, $uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = null)
    {
        if (in_array(strtoupper($method), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $this->startSession();
            $parameters['_token'] = $parameters['_token'] ?? app('session')->token();
        }

        return parent::call($method, $uri, $parameters, $cookies, $files, $server, $content);
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
