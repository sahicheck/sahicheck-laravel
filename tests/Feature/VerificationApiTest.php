<?php

declare(strict_types=1);

namespace SahiCheck\Laravel\Tests\Feature;

use SahiCheck\Facades\SahiCheck;
use SahiCheck\Http\HttpResponse;
use SahiCheck\Laravel\Tests\TestCase;
use SahiCheck\Response\EmailVerificationResult;
use SahiCheck\Response\IpLookupResult;
use SahiCheck\Response\PhoneVerificationResult;

class VerificationApiTest extends TestCase
{
    public function test_phone_facade_call(): void
    {
        $payload = [
            'success' => true,
            'data' => [
                'phone' => '+919876543210',
                'valid' => true,
                'country' => 'IN',
                'calling_code' => '+91',
                'carrier' => 'Bharti Airtel',
                'line_type' => 'mobile',
            ],
            'meta' => [
                'request_id' => 'req_phone_123',
                'timestamp' => '2026-10-03T12:00:00Z',
            ],
        ];

        $this->mockHttp->queueResponse(new HttpResponse(
            statusCode: 200,
            headers: ['Content-Type' => 'application/json', 'X-Request-Id' => 'req_phone_123'],
            body: json_encode($payload, JSON_THROW_ON_ERROR)
        ));

        $result = SahiCheck::phone()->validate('+919876543210', 'IN');

        $this->assertInstanceOf(PhoneVerificationResult::class, $result);
        $this->assertTrue($result->valid);
        $this->assertSame('+919876543210', $result->phone);
        $this->assertSame('IN', $result->country);
        $this->assertSame('Bharti Airtel', $result->carrier);
        $this->assertTrue($result->isSuccess());

        $lastRequest = $this->mockHttp->getLastRequest();
        $this->assertNotNull($lastRequest);
        $this->assertSame('POST', $lastRequest['method']);
        $this->assertSame('https://api.sahicheck.com/api/v1/phone/validate', $lastRequest['url']);
        $this->assertSame('vfy_test_mock_api_key_xyz', $lastRequest['headers']['X-API-Key']);
        $this->assertSame(['phone' => '+919876543210', 'country_code' => 'IN'], $lastRequest['body']);
    }

    public function test_email_facade_call(): void
    {
        $payload = [
            'success' => true,
            'data' => [
                'email' => 'customer@example.com',
                'valid' => true,
                'disposable' => false,
                'free' => false,
                'mx' => true,
                'syntax' => true,
                'score' => 0.95,
            ],
            'meta' => [
                'request_id' => 'req_email_456',
                'timestamp' => '2026-10-03T12:00:00Z',
            ],
        ];

        $this->mockHttp->queueResponse(new HttpResponse(
            statusCode: 200,
            headers: ['Content-Type' => 'application/json', 'X-Request-Id' => 'req_email_456'],
            body: json_encode($payload, JSON_THROW_ON_ERROR)
        ));

        $result = SahiCheck::email()->verify('customer@example.com');

        $this->assertInstanceOf(EmailVerificationResult::class, $result);
        $this->assertTrue($result->valid);
        $this->assertSame('customer@example.com', $result->email);
        $this->assertFalse($result->disposable);
        $this->assertFalse($result->free);
        $this->assertTrue($result->mx);
        $this->assertTrue($result->syntax);
        $this->assertSame(0.95, $result->score);

        $lastRequest = $this->mockHttp->getLastRequest();
        $this->assertNotNull($lastRequest);
        $this->assertSame('POST', $lastRequest['method']);
        $this->assertSame('https://api.sahicheck.com/api/v1/email/verify', $lastRequest['url']);
        $this->assertSame('vfy_test_mock_api_key_xyz', $lastRequest['headers']['X-API-Key']);
        $this->assertSame(['email' => 'customer@example.com'], $lastRequest['body']);
    }

    public function test_ip_facade_call(): void
    {
        $payload = [
            'success' => true,
            'data' => [
                'ip' => '8.8.8.8',
                'country_code' => 'US',
                'country' => 'United States',
                'region' => 'California',
                'city' => 'Mountain View',
                'asn' => 'AS15169',
                'organization' => 'Google LLC',
                'vpn' => false,
                'proxy' => false,
                'tor' => false,
            ],
            'meta' => [
                'request_id' => 'req_ip_789',
                'timestamp' => '2026-10-03T12:00:00Z',
            ],
        ];

        $this->mockHttp->queueResponse(new HttpResponse(
            statusCode: 200,
            headers: ['Content-Type' => 'application/json', 'X-Request-Id' => 'req_ip_789'],
            body: json_encode($payload, JSON_THROW_ON_ERROR)
        ));

        $result = SahiCheck::ip()->lookup('8.8.8.8');

        $this->assertInstanceOf(IpLookupResult::class, $result);
        $this->assertSame('8.8.8.8', $result->ip);
        $this->assertSame('US', $result->countryCode);
        $this->assertSame('United States', $result->country);
        $this->assertSame('Mountain View', $result->city);
        $this->assertSame('AS15169', $result->asn);
        $this->assertSame('Google LLC', $result->organization);
        $this->assertFalse($result->vpn);
        $this->assertFalse($result->proxy);
        $this->assertFalse($result->tor);

        $lastRequest = $this->mockHttp->getLastRequest();
        $this->assertNotNull($lastRequest);
        $this->assertSame('GET', $lastRequest['method']);
        $this->assertSame('https://api.sahicheck.com/api/v1/ip/lookup?ip=8.8.8.8', $lastRequest['url']);
        $this->assertSame('vfy_test_mock_api_key_xyz', $lastRequest['headers']['X-API-Key']);
    }

    public function test_request_id_forwarding(): void
    {
        $payload = [
            'success' => true,
            'data' => [
                'phone' => '+919876543210',
                'valid' => true,
            ],
            'meta' => [
                'request_id' => 'my-custom-request-id-42',
            ],
        ];

        $this->mockHttp->queueResponse(new HttpResponse(
            statusCode: 200,
            headers: ['Content-Type' => 'application/json', 'X-Request-Id' => 'my-custom-request-id-42'],
            body: json_encode($payload, JSON_THROW_ON_ERROR)
        ));

        $result = SahiCheck::withRequestId('my-custom-request-id-42')
            ->phone()
            ->validate('+919876543210', 'IN');

        $this->assertInstanceOf(PhoneVerificationResult::class, $result);

        $lastRequest = $this->mockHttp->getLastRequest();
        $this->assertNotNull($lastRequest);
        $this->assertArrayHasKey('X-Request-Id', $lastRequest['headers']);
        $this->assertSame('my-custom-request-id-42', $lastRequest['headers']['X-Request-Id']);
    }
}
