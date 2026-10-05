<?php

declare(strict_types=1);

namespace SahiCheck\Laravel\Tests\Feature;

use SahiCheck\Exception\AuthenticationException;
use SahiCheck\Exception\InsufficientCreditsException;
use SahiCheck\Exception\NetworkException;
use SahiCheck\Exception\RateLimitException;
use SahiCheck\Exception\ServerException;
use SahiCheck\Exception\ValidationException;
use SahiCheck\Facades\SahiCheck;
use SahiCheck\Http\HttpResponse;
use SahiCheck\Laravel\Tests\TestCase;

class ExceptionPropagationTest extends TestCase
{
    public function test_authentication_exception_propagates_on_401(): void
    {
        $this->mockHttp->queueResponse(new HttpResponse(
            statusCode: 401,
            headers: ['Content-Type' => 'application/json', 'X-Request-Id' => 'req_auth_err'],
            body: json_encode([
                'success' => false,
                'error' => [
                    'code' => 'INVALID_API_KEY',
                    'message' => 'The provided API key is invalid or revoked.',
                ],
            ], JSON_THROW_ON_ERROR)
        ));

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('The provided API key is invalid or revoked.');

        SahiCheck::phone()->validate('+919876543210');
    }

    public function test_insufficient_credits_exception_propagates_on_402(): void
    {
        $this->mockHttp->queueResponse(new HttpResponse(
            statusCode: 402,
            headers: ['Content-Type' => 'application/json', 'X-Request-Id' => 'req_credit_err'],
            body: json_encode([
                'success' => false,
                'error' => [
                    'code' => 'INSUFFICIENT_CREDITS',
                    'message' => 'Your account has insufficient credits.',
                ],
            ], JSON_THROW_ON_ERROR)
        ));

        $this->expectException(InsufficientCreditsException::class);
        $this->expectExceptionMessage('Your account has insufficient credits.');

        SahiCheck::email()->verify('user@example.com');
    }

    public function test_validation_exception_propagates_on_422(): void
    {
        $this->mockHttp->queueResponse(new HttpResponse(
            statusCode: 422,
            headers: ['Content-Type' => 'application/json', 'X-Request-Id' => 'req_val_err'],
            body: json_encode([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'The email format is invalid.',
                    'details' => ['email' => ['The email field must be a valid email address.']],
                ],
            ], JSON_THROW_ON_ERROR)
        ));

        try {
            SahiCheck::email()->verify('invalid-email');
            $this->fail('Expected ValidationException was not thrown.');
        } catch (ValidationException $e) {
            $this->assertSame(422, $e->getCode());
            $this->assertSame('The email format is invalid.', $e->getMessage());
            $this->assertSame('VALIDATION_ERROR', $e->getErrorCode());
            $this->assertSame(['email' => ['The email field must be a valid email address.']], $e->getDetails());
        }
    }

    public function test_rate_limit_exception_propagates_on_429(): void
    {
        $this->mockHttp->queueResponse(new HttpResponse(
            statusCode: 429,
            headers: [
                'Content-Type' => 'application/json',
                'Retry-After' => '30',
                'X-RateLimit-Limit' => '100',
                'X-RateLimit-Remaining' => '0',
            ],
            body: json_encode([
                'success' => false,
                'error' => [
                    'code' => 'RATE_LIMIT_EXCEEDED',
                    'message' => 'Rate limit exceeded. Try again in 30 seconds.',
                ],
            ], JSON_THROW_ON_ERROR)
        ));

        try {
            SahiCheck::ip()->lookup('1.1.1.1');
            $this->fail('Expected RateLimitException was not thrown.');
        } catch (RateLimitException $e) {
            $this->assertSame(429, $e->getCode());
            $this->assertSame(30, $e->getRetryAfter());
            $this->assertSame(100, $e->getRateLimit());
            $this->assertSame(0, $e->getRateLimitRemaining());
        }
    }

    public function test_server_exception_propagates_on_500(): void
    {
        $this->mockHttp->queueResponse(new HttpResponse(
            statusCode: 500,
            headers: ['Content-Type' => 'application/json'],
            body: json_encode([
                'success' => false,
                'error' => [
                    'code' => 'SERVER_ERROR',
                    'message' => 'Internal server error occurred.',
                ],
            ], JSON_THROW_ON_ERROR)
        ));

        $this->expectException(ServerException::class);
        $this->expectExceptionMessage('Internal server error occurred.');

        SahiCheck::phone()->validate('+919876543210');
    }

    public function test_network_exception_propagates_on_transport_failure(): void
    {
        $this->mockHttp->queueResponse(new NetworkException('Connection timed out after 10000 milliseconds.'));

        $this->expectException(NetworkException::class);
        $this->expectExceptionMessage('Connection timed out');

        SahiCheck::phone()->validate('+919876543210');
    }
}
