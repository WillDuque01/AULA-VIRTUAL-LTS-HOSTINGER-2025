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

        app()->setLocale($this->testingLocale);
        URL::defaults(['locale' => $this->testingLocale]);

        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
    }

    protected function getEnvironmentSetUp($app): void
    {
        $databasePath = database_path('testing.sqlite');

        if (! file_exists($databasePath)) {
            touch($databasePath);
        }

        $app['config']->set('app.locale', $this->testingLocale);
        $app['config']->set('session.driver', 'array');
        $app['config']->set('queue.default', 'sync');
        $app['config']->set('cache.default', 'array');
        $app['config']->set('mail.default', 'array');
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite.database', $databasePath);
        $app['config']->set('hashing.driver', 'bcrypt');
        $app['config']->set('hashing.bcrypt.verify', false);
        $app['config']->set('hashing.argon.verify', false);
        $app['config']->set('telescope.enabled', false);
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
