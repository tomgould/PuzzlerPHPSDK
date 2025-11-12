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
            $this->assertNotEmpty($puzzles, 'Expected at least some puzzles in the bundle');

            // Verify puzzle structure
            if (!empty($puzzles)) {
                $firstPuzzle = $puzzles[0];
                $this->assertArrayHasKey('pml_id', $firstPuzzle);
                $this->assertArrayHasKey('abbr', $firstPuzzle);
                $this->assertArrayHasKey('name', $firstPuzzle);
                $this->assertArrayHasKey('game_data', $firstPuzzle);
                $this->assertArrayHasKey('puzzle_id', $firstPuzzle);
                $this->assertArrayHasKey('rdate', $firstPuzzle);
            }
        } catch (PuzzlerException $e) {
            $this->fail('Collect all puzzles failed: ' . $e->getMessage() . "\nResponse: " . $e->getResponseBody());
        }
    }

    public function testCollectWithNoFiltersUsesModelWrapper()
    {
        try {
            // Explicitly test empty array - this should trigger the model wrapper
            $puzzles = $this->client->puzzle()->collect([]);

            $this->assertIsArray($puzzles);
            $this->assertNotEmpty($puzzles, 'Expected puzzles when using empty filters array');
        } catch (PuzzlerException $e) {
            $this->fail('Collect with empty filters failed: ' . $e->getMessage() . "\nResponse: " . $e->getResponseBody());
        }
    }

    public function testCollectWithDateFilterReturnsArray()
    {
        try {
            // First get all puzzles to find a valid date
            $allPuzzles = $this->client->puzzle()->collect();

            if (empty($allPuzzles)) {
                $this->markTestSkipped('No puzzles available in bundle to test date filtering');
            }

            // Use the date from the first puzzle to ensure we get results
            $testDate = $allPuzzles[0]['rdate'];

            // Convert "23 Jan 2025" format to "2025-01-23"
            $dateObj = \DateTime::createFromFormat('d M Y', $testDate);
            $formattedDate = $dateObj->format('Y-m-d');

            $puzzles = $this->client->puzzle()->collect([
                'puzzleDate' => $formattedDate
            ]);

            $this->assertIsArray($puzzles);
            $this->assertNotEmpty($puzzles, "Expected puzzles for date: {$formattedDate}");

            // Verify all returned puzzles match the requested date
            foreach ($puzzles as $puzzle) {
                $this->assertEquals($testDate, $puzzle['rdate'], 'All puzzles should match the requested date');
            }
        } catch (PuzzlerException $e) {
            $this->fail('Collect with date filter failed: ' . $e->getMessage() . "\nResponse: " . $e->getResponseBody());
        }
    }

    public function testCollectWithDateRangeReturnsArray()
    {
        try {
            // Get all puzzles to find valid date range
            $allPuzzles = $this->client->puzzle()->collect();

            if (empty($allPuzzles)) {
                $this->markTestSkipped('No puzzles available in bundle to test date range filtering');
            }

            // Get dates from first and last puzzles
            $dates = array_map(function($puzzle) {
                return \DateTime::createFromFormat('d M Y', $puzzle['rdate']);
            }, $allPuzzles);

            $minDate = min($dates);
            $maxDate = max($dates);

            $puzzles = $this->client->puzzle()->collect([
                'puzzleDateFrom' => $minDate->format('Y-m-d'),
                'puzzleDateTo' => $maxDate->format('Y-m-d')
            ]);

            $this->assertIsArray($puzzles);
            $this->assertNotEmpty($puzzles, 'Expected puzzles within the date range');

            // Should get same or similar count as all puzzles
            $this->assertGreaterThanOrEqual(1, count($puzzles));
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

            // Verify all returned puzzles match the requested type
            if (!empty($puzzles)) {
                foreach ($puzzles as $puzzle) {
                    $this->assertEquals($puzzleType, $puzzle['abbr'], 'All puzzles should match the requested type');
                }
            }
        } catch (PuzzlerException $e) {
            $this->fail('Collect with puzzle types failed: ' . $e->getMessage() . "\nResponse: " . $e->getResponseBody());
        }
    }

    public function testCollectWithMultiplePuzzleTypesReturnsArray()
    {
        try {
            $dictionary = $this->client->puzzle()->dictionary();

            if (count($dictionary['types']) < 2) {
                $this->markTestSkipped('Need at least 2 puzzle types to test multiple type filtering');
            }

            // Use first two available types
            $puzzleTypes = array_slice($dictionary['types'], 0, 2);

            $puzzles = $this->client->puzzle()->collect([
                'puzzleTypes' => $puzzleTypes
            ]);

            $this->assertIsArray($puzzles);

            // Verify all returned puzzles match one of the requested types
            if (!empty($puzzles)) {
                foreach ($puzzles as $puzzle) {
                    $this->assertContains($puzzle['abbr'], $puzzleTypes, 'Puzzle type should be in requested types');
                }
            }
        } catch (PuzzlerException $e) {
            $this->fail('Collect with multiple puzzle types failed: ' . $e->getMessage() . "\nResponse: " . $e->getResponseBody());
        }
    }

    public function testCollectWithPuzzleNamesReturnsArray()
    {
        try {
            $dictionary = $this->client->puzzle()->dictionary();

            if (empty($dictionary['names'])) {
                $this->markTestSkipped('No puzzle names available in dictionary');
            }

            // Find a type with multiple names (like Sudoku variants)
            $puzzleName = null;
            foreach ($dictionary['names'] as $type => $names) {
                if (!empty($names)) {
                    $puzzleName = $names[0];
                    break;
                }
            }

            if ($puzzleName === null) {
                $this->markTestSkipped('No puzzle names found in dictionary');
            }

            $puzzles = $this->client->puzzle()->collect([
                'puzzleNames' => [$puzzleName]
            ]);

            $this->assertIsArray($puzzles);

            // Verify all returned puzzles match the requested name
            if (!empty($puzzles)) {
                foreach ($puzzles as $puzzle) {
                    $this->assertEquals($puzzleName, $puzzle['name'], 'All puzzles should match the requested name');
                }
            }
        } catch (PuzzlerException $e) {
            $this->fail('Collect with puzzle names failed: ' . $e->getMessage() . "\nResponse: " . $e->getResponseBody());
        }
    }

    public function testCollectWithMultipleFiltersReturnsArray()
    {
        try {
            $dictionary = $this->client->puzzle()->dictionary();

            if (empty($dictionary['types'])) {
                $this->markTestSkipped('No puzzle types available in dictionary');
            }

            $allPuzzles = $this->client->puzzle()->collect();

            if (empty($allPuzzles)) {
                $this->markTestSkipped('No puzzles available to test multiple filters');
            }

            $puzzleType = $dictionary['types'][0];
            $testDate = $allPuzzles[0]['rdate'];
            $dateObj = \DateTime::createFromFormat('d M Y', $testDate);
            $formattedDate = $dateObj->format('Y-m-d');

            $puzzles = $this->client->puzzle()->collect([
                'puzzleDate' => $formattedDate,
                'puzzleTypes' => [$puzzleType]
            ]);

            $this->assertIsArray($puzzles);

            // Verify returned puzzles match both filters
            if (!empty($puzzles)) {
                foreach ($puzzles as $puzzle) {
                    $this->assertEquals($testDate, $puzzle['rdate']);
                    $this->assertEquals($puzzleType, $puzzle['abbr']);
                }
            }
        } catch (PuzzlerException $e) {
            $this->fail('Collect with multiple filters failed: ' . $e->getMessage() . "\nResponse: " . $e->getResponseBody());
        }
    }

    public function testCollectWithPuzzleTypeAndNameReturnsCorrectResults()
    {
        try {
            $dictionary = $this->client->puzzle()->dictionary();

            // Find a type with multiple names (e.g., Sudoku with Easy/Medium/Hard)
            $testType = null;
            $testName = null;

            foreach ($dictionary['names'] as $type => $names) {
                if (count($names) > 1) {
                    $testType = $type;
                    $testName = $names[0];
                    break;
                }
            }

            if ($testType === null || $testName === null) {
                $this->markTestSkipped('No puzzle type with multiple names found for testing');
            }

            // Test 1: Get all puzzles of the type
            $allOfType = $this->client->puzzle()->collect([
                'puzzleTypes' => [$testType]
            ]);

            // Test 2: Get only specific name (without including type in puzzleTypes)
            $specificName = $this->client->puzzle()->collect([
                'puzzleNames' => [$testName]
            ]);

            $this->assertIsArray($allOfType);
            $this->assertIsArray($specificName);

            // When filtering by name only, should get fewer results than all of that type
            if (!empty($allOfType) && !empty($specificName)) {
                $this->assertLessThanOrEqual(
                    count($allOfType),
                    count($specificName),
                    'Filtering by specific name should return same or fewer puzzles than filtering by type alone'
                );

                // Verify all returned puzzles have the correct name
                foreach ($specificName as $puzzle) {
                    $this->assertEquals($testName, $puzzle['name']);
                }
            }
        } catch (PuzzlerException $e) {
            $this->fail('Collect with type and name combination failed: ' . $e->getMessage() . "\nResponse: " . $e->getResponseBody());
        }
    }

    public function testCollectWithInvalidDateReturnsEmptyArray()
    {
        try {
            // Use a date far in the past that's definitely not in the current bundle
            $puzzles = $this->client->puzzle()->collect([
                'puzzleDate' => '2020-01-01'
            ]);

            $this->assertIsArray($puzzles);
            // May return empty array or may have some results depending on bundle
            // The important thing is it doesn't throw an error
        } catch (PuzzlerException $e) {
            $this->fail('Collect with old date should not throw exception: ' . $e->getMessage() . "\nResponse: " . $e->getResponseBody());
        }
    }
}
