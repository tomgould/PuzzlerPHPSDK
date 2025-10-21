<?php

namespace TomGould\PuzzlerPHPSDK\Tests\Integration;

use PHPUnit\Framework\TestCase;
use TomGould\PuzzlerPHPSDK\PuzzlerClient;
use TomGould\PuzzlerPHPSDK\Exception\PuzzlerException;

/**
 * Integration test for Puzzle Dictionary endpoint
 *
 * Set credentials in phpunit.xml or environment variables:
 * - PUZZLER_CLIENT_ID
 * - PUZZLER_API_KEY
 * - PUZZLER_SECRET_KEY
 * - PUZZLER_BASE_URL (optional)
 */
class PuzzleDictionaryTest extends TestCase
{
    private $client;

    protected function setUp(): void
    {
        $clientId = getenv('PUZZLER_CLIENT_ID');
        $apiKey = getenv('PUZZLER_API_KEY');
        $secretKey = getenv('PUZZLER_SECRET_KEY');
        $baseUrl = getenv('PUZZLER_BASE_URL') ?: 'https://rest-api-stage.puzzlerdigital.uk';

        if (empty($clientId) || empty($apiKey) || empty($secretKey)) {
            $this->markTestSkipped('Puzzler API credentials not configured. Set PUZZLER_CLIENT_ID, PUZZLER_API_KEY, and PUZZLER_SECRET_KEY environment variables.');
        }

        $this->client = new PuzzlerClient($clientId, $apiKey, $secretKey, $baseUrl);
    }

    public function testDictionaryReturnsValidStructure()
    {
        try {
            $dictionary = $this->client->puzzle()->dictionary();

            $this->assertIsArray($dictionary);
            $this->assertArrayHasKey('types', $dictionary);
            $this->assertArrayHasKey('names', $dictionary);

            // Verify types is an array
            $this->assertIsArray($dictionary['types']);
            $this->assertNotEmpty($dictionary['types']);

            // Verify names is an associative array
            $this->assertIsArray($dictionary['names']);
            $this->assertNotEmpty($dictionary['names']);

            // Verify each type in names has an array of names
            foreach ($dictionary['names'] as $type => $names) {
                $this->assertIsString($type);
                $this->assertIsArray($names);
            }
        } catch (PuzzlerException $e) {
            $this->fail('Dictionary request failed: ' . $e->getMessage() . "\nResponse: " . $e->getResponseBody());
        }
    }
}

