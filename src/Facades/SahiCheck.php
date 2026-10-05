<?php

declare(strict_types=1);

namespace SahiCheck\Facades;

use Illuminate\Support\Facades\Facade;
use SahiCheck\Email\EmailApi;
use SahiCheck\Http\HttpClientInterface;
use SahiCheck\Http\HttpResponse;
use SahiCheck\Ip\IpApi;
use SahiCheck\Phone\PhoneApi;
use SahiCheck\SahiCheckClient;

/**
 * @method static PhoneApi phone()
 * @method static EmailApi email()
 * @method static IpApi ip()
 * @method static SahiCheckClient withRequestId(?string $requestId)
 * @method static SahiCheckClient withTimeout(int $timeout)
 * @method static SahiCheckClient withBaseUrl(string $baseUrl)
 * @method static HttpResponse sendRequest(string $method, string $path, array $query = [], ?array $body = null)
 * @method static string getApiKey()
 * @method static string getBaseUrl()
 * @method static int getTimeout()
 * @method static string|null getRequestId()
 * @method static HttpClientInterface getHttpClient()
 *
 * @see \SahiCheck\SahiCheckClient
 */
class SahiCheck extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return SahiCheckClient::class;
    }
}
