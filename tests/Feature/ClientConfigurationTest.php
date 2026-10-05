<?php

declare(strict_types=1);

namespace SahiCheck\Laravel\Tests\Feature;

use SahiCheck\Exception\AuthenticationException;
use SahiCheck\Http\HttpResponse;
use SahiCheck\Laravel\Tests\TestCase;
use SahiCheck\SahiCheckClient;

class ClientConfigurationTest extends TestCase
{
    public function test_api_key_configuration(): void
    {
        $this->app['config']->set('sahicheck.api_key', 'vfy_custom_key_999');
        $this->app->forgetInstance(SahiCheckClient::class);

        $client = $this->app->make(SahiCheckClient::class);

        $this->assertSame('vfy_custom_key_999', $client->getApiKey());
    }

    public function test_base_url_configuration(): void
    {
        $this->app['config']->set('sahicheck.base_url', 'https://eu.sahicheck.com/');
        $this->app->forgetInstance(SahiCheckClient::class);

        $client = $this->app->make(SahiCheckClient::class);

        $this->assertSame('https://eu.sahicheck.com', $client->getBaseUrl());
    }

    public function test_timeout_configuration(): void
    {
        $this->app['config']->set('sahicheck.timeout', 45);
        $this->app->forgetInstance(SahiCheckClient::class);

        $client = $this->app->make(SahiCheckClient::class);

        $this->assertSame(45, $client->getTimeout());
    }

    public function test_missing_api_key_defaults_gracefully_on_resolution(): void
    {
        $this->app['config']->set('sahicheck.api_key', null);
        $this->app->forgetInstance(SahiCheckClient::class);

        // Container resolution must not crash when API key is missing
        $client = $this->app->make(SahiCheckClient::class);

        $this->assertInstanceOf(SahiCheckClient::class, $client);
        $this->assertSame('', $client->getApiKey());
    }

    public function test_missing_api_key_propagates_authentication_exception_on_request(): void
    {
        $this->app['config']->set('sahicheck.api_key', '');
        $this->app->forgetInstance(SahiCheckClient::class);

        $client = $this->app->make(SahiCheckClient::class);

        $this->mockHttp->queueResponse(new HttpResponse(
            statusCode: 401,
            headers: ['Content-Type' => 'application/json'],
            body: json_encode([
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHORIZED',
                    'message' => 'Invalid or missing API key.',
                ],
            ], JSON_THROW_ON_ERROR)
        ));

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Invalid or missing API key.');

        $client->phone()->validate('+919876543210');
    }

    public function test_api_key_is_redacted_in_debug_info(): void
    {
        $client = $this->app->make(SahiCheckClient::class);
        $debug = $client->__debugInfo();

        $this->assertSame('***[REDACTED]***', $debug['apiKey']);
        $this->assertArrayNotHasKey('vfy_test_mock_api_key_xyz', $debug);
    }
}
