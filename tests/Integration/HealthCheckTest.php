<?php

namespace TomGould\PuzzlerPHPSDK\Tests\Integration;

use PHPUnit\Framework\TestCase;
use TomGould\PuzzlerPHPSDK\PuzzlerClient;
use TomGould\PuzzlerPHPSDK\Exception\PuzzlerException;

/**
 * Integration test for Health Check endpoint
 *
 * Set credentials in phpunit.xml or environment variables:
 * - PUZZLER_CLIENT_ID
 * - PUZZLER_API_KEY
 * - PUZZLER_SECRET_KEY
 * - PUZZLER_BASE_URL (optional)
 */
class HealthCheckTest extends TestCase
{
    private $client;

    protected function setUp(): void
    {
        $clientId = getenv('PUZZLER_CLIENT_ID');
        $apiKey = getenv('PUZZLER_API_KEY');
        $secretKey = getenv('PUZZLER_SECRET_KEY');
        $baseUrl = getenv('PUZZLER_BASE_URL') ?: 'https://rest-api.puzzlerdigital.uk';

        if (empty($clientId) || empty($apiKey) || empty($secretKey)) {
            $this->markTestSkipped('Puzzler API credentials not configured. Set PUZZLER_CLIENT_ID, PUZZLER_API_KEY, and PUZZLER_SECRET_KEY environment variables.');
        }

        $this->client = new PuzzlerClient($clientId, $apiKey, $secretKey, $baseUrl);
    }

    public function testHealthCheckReturnsSuccessMessage()
    {
        try {
            $result = $this->client->health()->check();

            $this->assertIsString($result);
            $this->assertNotEmpty($result);
            $this->assertStringContainsString('health', strtolower($result));
        } catch (PuzzlerException $e) {
            $this->fail('Health check failed: ' . $e->getMessage() . "\nResponse: " . $e->getResponseBody());
        }
    }
}

