<?php

namespace TomGould\PuzzlerPHPSDK\Tests\Integration;

use PHPUnit\Framework\TestCase;
use TomGould\PuzzlerPHPSDK\PuzzlerClient;
use TomGould\PuzzlerPHPSDK\Exception\PuzzlerException;

/**
 * Integration test for Puzzle Collect endpoint
 *
 * Set credentials in phpunit.xml or environment variables:
 * - PUZZLER_CLIENT_ID
 * - PUZZLER_API_KEY
 * - PUZZLER_SECRET_KEY
 * - PUZZLER_BASE_URL (optional)
 */
class PuzzleCollectTest extends TestCase
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

    public function testCollectAllPuzzlesReturnsArray()
    {
        try {
            $puzzles = $this->client->puzzle()->collect();

            $this->assertIsArray($puzzles);
        } catch (PuzzlerException $e) {
            $this->fail('Collect all puzzles failed: ' . $e->getMessage() . "\nResponse: " . $e->getResponseBody());
        }
    }

    public function testCollectWithDateFilterReturnsArray()
    {
        try {
            $puzzles = $this->client->puzzle()->collect([
                'puzzleDate' => date('Y-m-d')
            ]);

            $this->assertIsArray($puzzles);
        } catch (PuzzlerException $e) {
            $this->fail('Collect with date filter failed: ' . $e->getMessage() . "\nResponse: " . $e->getResponseBody());
        }
    }

    public function testCollectWithDateRangeReturnsArray()
    {
        try {
            $today = date('Y-m-d');
            $tomorrow = date('Y-m-d', strtotime('+1 day'));

            $puzzles = $this->client->puzzle()->collect([
                'puzzleDateFrom' => $today,
                'puzzleDateTo' => $tomorrow
            ]);

            $this->assertIsArray($puzzles);
        } catch (PuzzlerException $e) {
            $this->fail('Collect with date range failed: ' . $e->getMessage() . "\nResponse: " . $e->getResponseBody());
        }
    }

    public function testCollectWithPuzzleTypesReturnsArray()
    {
        try {
            // First get available types
            $dictionary = $this->client->puzzle()->dictionary();

            if (empty($dictionary['types'])) {
                $this->markTestSkipped('No puzzle types available in dictionary');
            }

            // Use first available type
            $puzzleType = $dictionary['types'][0];

            $puzzles = $this->client->puzzle()->collect([
                'puzzleTypes' => [$puzzleType]
            ]);

            $this->assertIsArray($puzzles);
        } catch (PuzzlerException $e) {
            $this->fail('Collect with puzzle types failed: ' . $e->getMessage() . "\nResponse: " . $e->getResponseBody());
        }
    }

    public function testCollectWithMultipleFiltersReturnsArray()
    {
        try {
            $dictionary = $this->client->puzzle()->dictionary();

            if (empty($dictionary['types'])) {
                $this->markTestSkipped('No puzzle types available in dictionary');
            }

            $puzzleType = $dictionary['types'][0];

            $puzzles = $this->client->puzzle()->collect([
                'puzzleDate' => date('Y-m-d'),
                'puzzleTypes' => [$puzzleType]
            ]);

            $this->assertIsArray($puzzles);
        } catch (PuzzlerException $e) {
            $this->fail('Collect with multiple filters failed: ' . $e->getMessage() . "\nResponse: " . $e->getResponseBody());
        }
    }
}

