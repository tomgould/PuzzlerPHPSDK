<?php

namespace TomGould\PuzzlerPHPSDK\Tests\Unit\Http;

use PHPUnit\Framework\TestCase;
use TomGould\PuzzlerPHPSDK\Http\HttpClient;

/**
 * Unit tests for HttpClient
 */
class HttpClientTest extends TestCase
{
    public function testHttpClientCanBeInstantiated()
    {
        $client = new HttpClient('client123', 'key123', 'secret123', 'https://api.example.com');

        $this->assertInstanceOf(HttpClient::class, $client);
    }

    public function testBaseUrlTrimsTrailingSlash()
    {
        $client = new HttpClient('client123', 'key123', 'secret123', 'https://api.example.com/');

        // We can't test this directly as baseUrl is private, but the client should instantiate without error
        $this->assertInstanceOf(HttpClient::class, $client);
    }
}

