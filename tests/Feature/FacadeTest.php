<?php

declare(strict_types=1);

namespace SahiCheck\Laravel\Tests\Feature;

use SahiCheck\Email\EmailApi;
use SahiCheck\Facades\SahiCheck;
use SahiCheck\Ip\IpApi;
use SahiCheck\Laravel\Tests\TestCase;
use SahiCheck\Phone\PhoneApi;
use SahiCheck\SahiCheckClient;

class FacadeTest extends TestCase
{
    public function test_facade_resolves_sahicheck_client(): void
    {
        $root = SahiCheck::getFacadeRoot();

        $this->assertInstanceOf(SahiCheckClient::class, $root);
    }

    public function test_facade_resolves_phone_api(): void
    {
        $phoneApi = SahiCheck::phone();

        $this->assertInstanceOf(PhoneApi::class, $phoneApi);
    }

    public function test_facade_resolves_email_api(): void
    {
        $emailApi = SahiCheck::email();

        $this->assertInstanceOf(EmailApi::class, $emailApi);
    }

    public function test_facade_resolves_ip_api(): void
    {
        $ipApi = SahiCheck::ip();

        $this->assertInstanceOf(IpApi::class, $ipApi);
    }

    public function test_facade_getters_match_configuration(): void
    {
        $this->assertSame('vfy_test_mock_api_key_xyz', SahiCheck::getApiKey());
        $this->assertSame('https://api.sahicheck.com', SahiCheck::getBaseUrl());
        $this->assertSame(10, SahiCheck::getTimeout());
    }

    public function test_facade_can_be_swapped_with_mock(): void
    {
        $customClient = new SahiCheckClient(
            apiKey: 'vfy_swapped_key',
            baseUrl: 'https://swapped.sahicheck.com',
            timeout: 5
        );

        SahiCheck::swap($customClient);

        $this->assertSame('vfy_swapped_key', SahiCheck::getApiKey());
        $this->assertSame('https://swapped.sahicheck.com', SahiCheck::getBaseUrl());
        $this->assertSame(5, SahiCheck::getTimeout());
    }

    public function test_facade_fluent_with_timeout_and_base_url(): void
    {
        $customTimeoutClient = SahiCheck::withTimeout(30);
        $this->assertInstanceOf(SahiCheckClient::class, $customTimeoutClient);
        $this->assertSame(30, $customTimeoutClient->getTimeout());

        $customBaseUrlClient = SahiCheck::withBaseUrl('https://custom.sahicheck.com');
        $this->assertInstanceOf(SahiCheckClient::class, $customBaseUrlClient);
        $this->assertSame('https://custom.sahicheck.com', $customBaseUrlClient->getBaseUrl());
    }
}
