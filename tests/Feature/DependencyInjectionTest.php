<?php

declare(strict_types=1);

namespace SahiCheck\Laravel\Tests\Feature;

use SahiCheck\Http\HttpResponse;
use SahiCheck\Laravel\Tests\TestCase;
use SahiCheck\Response\PhoneVerificationResult;
use SahiCheck\SahiCheckClient;

class VerificationServiceStub
{
    public function __construct(
        private readonly SahiCheckClient $sahiCheck
    ) {}

    public function verifyPhone(string $phone, ?string $countryCode = null): PhoneVerificationResult
    {
        return $this->sahiCheck
            ->phone()
            ->validate($phone, $countryCode);
    }

    public function getClient(): SahiCheckClient
    {
        return $this->sahiCheck;
    }
}

class DependencyInjectionTest extends TestCase
{
    public function test_container_auto_wires_sahicheck_client_into_service(): void
    {
        $service = $this->app->make(VerificationServiceStub::class);

        $this->assertInstanceOf(VerificationServiceStub::class, $service);
        $this->assertInstanceOf(SahiCheckClient::class, $service->getClient());
        $this->assertSame($this->app->make(SahiCheckClient::class), $service->getClient());
    }

    public function test_dependency_injected_service_executes_api_call(): void
    {
        $payload = [
            'success' => true,
            'data' => [
                'phone' => '+919876543210',
                'valid' => true,
                'country_code' => 'IN',
                'carrier' => 'Airtel',
                'line_type' => 'mobile',
            ],
            'meta' => [
                'request_id' => 'req_di_123',
            ],
        ];

        $this->mockHttp->queueResponse(new HttpResponse(
            statusCode: 200,
            headers: ['Content-Type' => 'application/json'],
            body: json_encode($payload, JSON_THROW_ON_ERROR)
        ));

        $service = $this->app->make(VerificationServiceStub::class);
        $result = $service->verifyPhone('+919876543210', 'IN');

        $this->assertTrue($result->valid);
        $this->assertSame('+919876543210', $result->phone);

        $lastRequest = $this->mockHttp->getLastRequest();
        $this->assertNotNull($lastRequest);
        $this->assertSame('POST', $lastRequest['method']);
        $this->assertSame(['phone' => '+919876543210', 'country_code' => 'IN'], $lastRequest['body']);
    }
}
