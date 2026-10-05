<?php

declare(strict_types=1);

namespace SahiCheck\Laravel\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use SahiCheck\Facades\SahiCheck;
use SahiCheck\Http\HttpClientInterface;
use SahiCheck\Laravel\Tests\Support\MockHttpClient;
use SahiCheck\SahiCheckServiceProvider;

abstract class TestCase extends OrchestraTestCase
{
    protected MockHttpClient $mockHttp;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockHttp = new MockHttpClient();
        $this->app->instance(HttpClientInterface::class, $this->mockHttp);
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            SahiCheckServiceProvider::class,
        ];
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     * @return array<string, class-string>
     */
    protected function getPackageAliases($app): array
    {
        return [
            'SahiCheck' => SahiCheck::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application $app
     */
    protected function defineEnvironment($app): void
    {
        $app['config']->set('sahicheck.api_key', 'vfy_test_mock_api_key_xyz');
        $app['config']->set('sahicheck.base_url', 'https://api.sahicheck.com');
        $app['config']->set('sahicheck.timeout', 10);
    }
}
