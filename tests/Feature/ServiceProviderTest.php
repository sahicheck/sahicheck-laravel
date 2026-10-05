<?php

declare(strict_types=1);

namespace SahiCheck\Laravel\Tests\Feature;

use Illuminate\Support\Facades\File;
use SahiCheck\Laravel\Tests\TestCase;
use SahiCheck\SahiCheckClient;
use SahiCheck\SahiCheckServiceProvider;

class ServiceProviderTest extends TestCase
{
    public function test_service_provider_is_registered(): void
    {
        $providers = $this->app->getLoadedProviders();

        $this->assertArrayHasKey(SahiCheckServiceProvider::class, $providers);
    }

    public function test_configuration_is_loaded_and_merged(): void
    {
        $this->assertTrue($this->app['config']->has('sahicheck'));
        $this->assertSame('vfy_test_mock_api_key_xyz', $this->app['config']->get('sahicheck.api_key'));
        $this->assertSame('https://api.sahicheck.com', $this->app['config']->get('sahicheck.base_url'));
        $this->assertSame(10, $this->app['config']->get('sahicheck.timeout'));
    }

    public function test_container_resolves_sahicheck_client(): void
    {
        $client = $this->app->make(SahiCheckClient::class);

        $this->assertInstanceOf(SahiCheckClient::class, $client);
    }

    public function test_container_resolves_sahicheck_alias(): void
    {
        $client = $this->app->make('sahicheck');

        $this->assertInstanceOf(SahiCheckClient::class, $client);
        $this->assertSame($this->app->make(SahiCheckClient::class), $client);
    }

    public function test_client_is_registered_as_singleton(): void
    {
        $client1 = $this->app->make(SahiCheckClient::class);
        $client2 = $this->app->make(SahiCheckClient::class);

        $this->assertSame($client1, $client2);
    }

    public function test_configuration_can_be_published(): void
    {
        $targetPath = $this->app->configPath('sahicheck.php');

        if (File::exists($targetPath)) {
            File::delete($targetPath);
        }

        $this->artisan('vendor:publish', ['--tag' => 'sahicheck-config'])
            ->assertExitCode(0);

        $this->assertFileExists($targetPath);

        // Clean up published config
        if (File::exists($targetPath)) {
            File::delete($targetPath);
        }
    }

    public function test_service_provider_provides_client_and_alias(): void
    {
        $provider = new SahiCheckServiceProvider($this->app);
        $provides = $provider->provides();

        $this->assertContains(SahiCheckClient::class, $provides);
        $this->assertContains('sahicheck', $provides);
    }
}
