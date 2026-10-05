<?php

declare(strict_types=1);

namespace SahiCheck\Laravel\Tests\Unit;

use PHPUnit\Framework\TestCase as PhpUnitTestCase;

class ConfigurationTest extends PhpUnitTestCase
{
    public function test_configuration_file_returns_expected_keys(): void
    {
        $config = require __DIR__ . '/../../config/sahicheck.php';

        $this->assertIsArray($config);
        $this->assertArrayHasKey('api_key', $config);
        $this->assertArrayHasKey('base_url', $config);
        $this->assertArrayHasKey('timeout', $config);
    }

    public function test_default_configuration_values(): void
    {
        putenv('SAHICHECK_API_KEY');
        putenv('SAHICHECK_BASE_URL');
        putenv('SAHICHECK_TIMEOUT');
        unset($_ENV['SAHICHECK_API_KEY'], $_ENV['SAHICHECK_BASE_URL'], $_ENV['SAHICHECK_TIMEOUT']);

        $config = require __DIR__ . '/../../config/sahicheck.php';

        $this->assertNull($config['api_key']);
        $this->assertSame('https://api.sahicheck.com', $config['base_url']);
        $this->assertSame(10, $config['timeout']);
    }

    public function test_environment_variables_override_defaults(): void
    {
        putenv('SAHICHECK_API_KEY=vfy_env_test_key_123');
        putenv('SAHICHECK_BASE_URL=https://custom-api.sahicheck.com');
        putenv('SAHICHECK_TIMEOUT=25');

        $config = require __DIR__ . '/../../config/sahicheck.php';

        $this->assertSame('vfy_env_test_key_123', $config['api_key']);
        $this->assertSame('https://custom-api.sahicheck.com', $config['base_url']);
        $this->assertSame(25, $config['timeout']);

        // Clean up
        putenv('SAHICHECK_API_KEY');
        putenv('SAHICHECK_BASE_URL');
        putenv('SAHICHECK_TIMEOUT');
    }
}
