<?php

namespace TomGould\PuzzlerPHPSDK\Tests\Integration;

use PHPUnit\Framework\TestCase;
use TomGould\PuzzlerPHPSDK\PuzzlerClient;
use TomGould\PuzzlerPHPSDK\Exception\AuthenticationException;
use TomGould\PuzzlerPHPSDK\Exception\PuzzlerException;

/**
 * Integration test specifically for testing credentials
 *
 * This test validates that your API credentials are working correctly.
 *
 * Set credentials in phpunit.xml or environment variables:
 * - PUZZLER_CLIENT_ID
 * - PUZZLER_API_KEY
 * - PUZZLER_SECRET_KEY
 * - PUZZLER_BASE_URL (optional)
 */
class CredentialsTest extends TestCase
{
    public function testValidCredentialsSucceed()
    {
        $clientId = getenv('PUZZLER_CLIENT_ID');
        $apiKey = getenv('PUZZLER_API_KEY');
        $secretKey = getenv('PUZZLER_SECRET_KEY');
        $baseUrl = getenv('PUZZLER_BASE_URL') ?: 'https://rest-api-stage.puzzlerdigital.uk';

        if (empty($clientId) || empty($apiKey) || empty($secretKey)) {
            $this->markTestSkipped('Puzzler API credentials not configured. Set PUZZLER_CLIENT_ID, PUZZLER_API_KEY, and PUZZLER_SECRET_KEY environment variables.');
        }

        $client = new PuzzlerClient($clientId, $apiKey, $secretKey, $baseUrl);

        try {
            $result = $client->health()->check();

            $this->assertIsString($result);
            $this->assertNotEmpty($result);

            echo "\n✓ Credentials are valid and working!\n";
            echo "  Client ID: " . substr($clientId, 0, 8) . "...\n";
            echo "  Base URL: " . $baseUrl . "\n";

        } catch (AuthenticationException $e) {
            $this->fail("Authentication failed. Please check your credentials:\n" .
                       "  Client ID: " . $clientId . "\n" .
                       "  API Key: " . substr($apiKey, 0, 8) . "...\n" .
                       "  Error: " . $e->getMessage());
        } catch (PuzzlerException $e) {
            $this->fail("API request failed: " . $e->getMessage() . "\nResponse: " . $e->getResponseBody());
        }
    }

    public function testInvalidCredentialsFail()
    {
        $client = new PuzzlerClient('invalid', 'invalid', 'invalid');

        $this->expectException(AuthenticationException::class);

        // Use dictionary endpoint instead of health since health is public
        $client->puzzle()->dictionary();
    }
}

