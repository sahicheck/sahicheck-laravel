<?php

declare(strict_types=1);

namespace SahiCheck;

use Illuminate\Support\ServiceProvider;
use SahiCheck\Http\HttpClientInterface;

class SahiCheckServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/sahicheck.php',
            'sahicheck'
        );

        $this->app->singleton(SahiCheckClient::class, function ($app): SahiCheckClient {
            $config = $app['config']->get('sahicheck', []);

            $apiKey = (string) ($config['api_key'] ?? '');
            $baseUrl = (string) ($config['base_url'] ?? SahiCheckClient::DEFAULT_BASE_URL);
            $timeout = (int) ($config['timeout'] ?? SahiCheckClient::DEFAULT_TIMEOUT);

            $httpClient = $app->bound(HttpClientInterface::class)
                ? $app->make(HttpClientInterface::class)
                : null;

            return new SahiCheckClient(
                apiKey: $apiKey,
                baseUrl: $baseUrl,
                timeout: $timeout,
                httpClient: $httpClient
            );
        });

        $this->app->alias(SahiCheckClient::class, 'sahicheck');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/sahicheck.php' => $this->app->configPath('sahicheck.php'),
            ], 'sahicheck-config');
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [
            SahiCheckClient::class,
            'sahicheck',
        ];
    }
}
