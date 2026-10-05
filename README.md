# SahiCheck Laravel SDK

Official Laravel integration for the SahiCheck verification API.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/sahicheck/sahicheck-laravel.svg?style=flat-square)](https://packagist.org/packages/sahicheck/sahicheck-laravel)
[![Total Downloads](https://img.shields.io/packagist/dt/sahicheck/sahicheck-laravel.svg?style=flat-square)](https://packagist.org/packages/sahicheck/sahicheck-laravel)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

A clean, production-ready Laravel wrapper around [`sahicheck/sahicheck-php`](https://github.com/sahicheck/sahicheck-php) that provides native Laravel service provider registration, configuration publishing, facade support, dependency injection, and comprehensive exception handling for phone validation, email verification, and IP intelligence.

---

## Requirements

- **PHP**: `>= 8.2`
- **Laravel Framework**: `10.x`, `11.x`, `12.x`, or `13.x`
- **cURL Extension**: Enabled (`ext-curl`)
- **JSON Extension**: Enabled (`ext-json`)
- **Generic PHP SDK**: [`sahicheck/sahicheck-php`](https://github.com/sahicheck/sahicheck-php) (`^0.1.0`)

---

## Installation

Install the package via Composer:

```bash
composer require sahicheck/sahicheck-laravel
```

### Publish Configuration

Publish the configuration file using `artisan`:

```bash
php artisan vendor:publish --tag=sahicheck-config
```

This will create `config/sahicheck.php` in your Laravel application.

### Environment Configuration

Add your SahiCheck API key to your `.env` file:

```env
SAHICHECK_API_KEY=vfy_live_xxxxxxxxx
```

Optional settings:

```env
# Optional: Override base API URL (defaults to https://api.sahicheck.com)
SAHICHECK_BASE_URL=https://api.sahicheck.com

# Optional: HTTP request timeout in seconds (defaults to 10)
SAHICHECK_TIMEOUT=10
```

---

## Configuration Reference

The published `config/sahicheck.php` file contains the following settings:

```php
return [
    /*
    |--------------------------------------------------------------------------
    | SahiCheck API Key
    |--------------------------------------------------------------------------
    |
    | Your SahiCheck API key for authentication. Get your key from the
    | SahiCheck dashboard: https://sahicheck.com/dashboard
    |
    */
    'api_key' => env('SAHICHECK_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | SahiCheck API Base URL
    |--------------------------------------------------------------------------
    |
    | The base URL for the SahiCheck REST API. Defaults to the production URL.
    |
    */
    'base_url' => env('SAHICHECK_BASE_URL', 'https://api.sahicheck.com'),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | The maximum number of seconds to wait for responses from SahiCheck.
    |
    */
    'timeout' => (int) env('SAHICHECK_TIMEOUT', 10),
];
```

The package is fully compatible with Laravel's configuration caching commands:

```bash
php artisan config:cache
php artisan config:clear
```

---

## Usage

### 1. Phone Verification

Validate phone numbers in international E.164 or national format:

```php
use SahiCheck\Facades\SahiCheck;

$result = SahiCheck::phone()->validate(
    '+919876543210',
    'IN'
);

if ($result->valid) {
    echo "Phone: " . $result->phone . "\n";
    echo "Country: " . $result->country . "\n";
    echo "Carrier: " . $result->carrier . "\n";
    echo "Line Type: " . $result->type . "\n";
    echo "National Format: " . $result->nationalFormat . "\n";
}
```

### 2. Email Verification

Verify email deliverability, check for disposable domains, and evaluate risk:

```php
use SahiCheck\Facades\SahiCheck;

$result = SahiCheck::email()->verify(
    'customer@example.com'
);

if ($result->valid) {
    echo "Email: " . $result->email . "\n";
    echo "Domain: " . $result->domain . "\n";
    echo "Disposable: " . ($result->disposable ? 'Yes' : 'No') . "\n";
    echo "Free Provider: " . ($result->free ? 'Yes' : 'No') . "\n";
    echo "MX Record Found: " . ($result->mx ? 'Yes' : 'No') . "\n";
    echo "Quality Score: " . $result->score . "\n";
}
```

### 3. IP Intelligence

Look up geolocation, ASN, and risk intelligence for any IPv4 or IPv6 address:

```php
use SahiCheck\Facades\SahiCheck;

$result = SahiCheck::ip()->lookup(
    '8.8.8.8'
);

echo "IP: " . $result->ip . "\n";
echo "Country: " . $result->country . " (" . $result->countryCode . ")\n";
echo "City: " . $result->city . "\n";
echo "ASN: " . $result->asn . " (" . $result->organization . ")\n";
echo "VPN: " . ($result->vpn ? 'Yes' : 'No') . "\n";
echo "Proxy: " . ($result->proxy ? 'Yes' : 'No') . "\n";
echo "Tor: " . ($result->tor ? 'Yes' : 'No') . "\n";
```

---

## Request ID Support

The underlying SDK supports correlating requests with custom client-provided request IDs for tracing and audit logs:

```php
use SahiCheck\Facades\SahiCheck;

$result = SahiCheck::withRequestId('req_signup_user_1024')
    ->phone()
    ->validate('+919876543210', 'IN');
```

---

## Dependency Injection

You can inject `SahiCheck\SahiCheckClient` directly into controllers, jobs, and application services. Laravel resolves and auto-wires the client instance automatically from the container:

```php
namespace App\Services;

use SahiCheck\Response\PhoneVerificationResult;
use SahiCheck\SahiCheckClient;

class UserRegistrationService
{
    public function __construct(
        private readonly SahiCheckClient $sahiCheck
    ) {}

    public function verifyUserPhone(string $phone, ?string $countryCode = null): PhoneVerificationResult
    {
        return $this->sahiCheck
            ->phone()
            ->validate($phone, $countryCode);
    }
}
```

---

## Exception Handling

All exceptions thrown by this package are re-used directly from `sahicheck/sahicheck-php`:

```php
use SahiCheck\Exception\ApiException;
use SahiCheck\Exception\AuthenticationException;
use SahiCheck\Exception\InsufficientCreditsException;
use SahiCheck\Exception\NetworkException;
use SahiCheck\Exception\RateLimitException;
use SahiCheck\Exception\ServerException;
use SahiCheck\Exception\ValidationException;
use SahiCheck\Facades\SahiCheck;

try {
    $result = SahiCheck::phone()->validate('+919876543210', 'IN');
} catch (AuthenticationException $e) {
    // 401 Unauthorized: Invalid or revoked API key
    Log::error('SahiCheck authentication error: ' . $e->getMessage());
} catch (InsufficientCreditsException $e) {
    // 402 Payment Required: Account out of credits
    Log::alert('SahiCheck out of credits: ' . $e->getMessage());
} catch (ValidationException $e) {
    // 422 Unprocessable Entity: Input validation failure
    $errors = $e->getDetails();
    Log::warning('Validation failed: ' . json_encode($errors));
} catch (RateLimitException $e) {
    // 429 Too Many Requests: Rate limit exceeded
    $retryAfterSeconds = $e->getRetryAfter();
    Log::warning('Rate limited. Retry after: ' . $retryAfterSeconds);
} catch (ServerException $e) {
    // 500/502/503/504: SahiCheck API or upstream gateway error
    Log::error('SahiCheck server error [' . $e->getCode() . ']: ' . $e->getMessage());
} catch (NetworkException $e) {
    // cURL timeout or network unreachable
    Log::error('Network transport error: ' . $e->getMessage());
} catch (ApiException $e) {
    // Generic API error
    Log::error('API error: ' . $e->getMessage());
}
```

---

## Testing in Your Application

When writing tests for your Laravel application, you can mock or swap the SahiCheck client using Laravel's standard facade testing utilities:

```php
use SahiCheck\Facades\SahiCheck;
use SahiCheck\SahiCheckClient;

public function test_user_registration_verification(): void
{
    $mockClient = Mockery::mock(SahiCheckClient::class);
    // configure expectations on $mockClient
    SahiCheck::swap($mockClient);

    // Run your application tests...
}
```

Alternatively, you can bind a custom `SahiCheck\Http\HttpClientInterface` in your test environment to test responses without making external API calls.

---

## Links & Resources

- [SahiCheck Verification API Documentation](https://sahicheck.com/docs)
- [SahiCheck PHP SDK (`sahicheck/sahicheck-php`)](https://github.com/sahicheck/sahicheck-php)
- [GitHub Repository](https://github.com/sahicheck/sahicheck-laravel)

---

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
