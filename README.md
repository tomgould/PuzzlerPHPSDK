# PuzzlerPHPSDK

A professional PHP SDK for the Puzzler Media REST API.

[![PHP Version](https://img.shields.io/badge/php-%3E%3D7.4-blue.svg)](https://php.net/)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

## Installation

Install via Composer:

```bash
composer require tomgould/puzzlerphpsdk
```

## Requirements

- PHP 7.4 or higher
- cURL extension
- JSON extension

## Quick Start

```php
<?php

require_once 'vendor/autoload.php';

use TomGould\PuzzlerPHPSDK\PuzzlerClient;
use TomGould\PuzzlerPHPSDK\Exception\PuzzlerException;

// Initialize the client
$client = new PuzzlerClient(
    'YOUR_CLIENT_ID',
    'YOUR_API_KEY',
    'YOUR_SECRET_KEY'
);

// Check API health
try {
    $health = $client->health()->check();
    echo $health; // "I am healthly!"
} catch (PuzzlerException $e) {
    echo "Error: " . $e->getMessage();
}

// Get all puzzles
try {
    $puzzles = $client->puzzle()->collect();
    print_r($puzzles);
} catch (PuzzlerException $e) {
    echo "Error: " . $e->getMessage();
}
```

## Usage

### Initialize Client

```php
use TomGould\PuzzlerPHPSDK\PuzzlerClient;

$client = new PuzzlerClient(
    'YOUR_CLIENT_ID',      // X-Client-Id
    'YOUR_API_KEY',        // X-Api-Key
    'YOUR_SECRET_KEY',     // Secret Key
    'https://rest-api.puzzlerdigital.uk' // Optional: Base URL
);
```

### Health Check

```php
$health = $client->health()->check();
```

### Get Puzzle Dictionary

The dictionary endpoint returns all available puzzle types and names from the latest bundle.

```php
$dictionary = $client->puzzle()->dictionary();

// Available puzzle types
print_r($dictionary['types']); // ['XW', 'SU', 'WS', ...]

// Available puzzle names by type
print_r($dictionary['names']); // ['SU' => ['Sudoku'], ...]
```

**Example response:**
```php
[
    'types' => ['XW', 'XC', 'WW', 'WS', 'SW', 'SU', 'JS', 'HM', 'AU'],
    'names' => [
        'XW' => ['Crossword'],
        'XC' => ['Cryptic Crossword'],
        'WW' => ['Word Wheel'],
        'WS' => ['Wordsearch'],
        'SW' => ['Splitwords'],
        'SU' => ['Hard Sudoku', 'Medium Sudoku', 'Easy Sudoku'],
        'JS' => ['Jigsaw'],
        'HM' => ['Hangman'],
        'AU' => ['Add Up']
    ]
]
```

### Collect Puzzles

#### Get all puzzles from the latest bundle

```php
$puzzles = $client->puzzle()->collect();
```

#### Filter by specific date

```php
$puzzles = $client->puzzle()->collect([
    'puzzleDate' => '2025-11-12'
]);
```

**Note:** Date must be in YYYY-MM-DD format. The API returns puzzles from the latest bundle, so requesting dates outside the bundle's range will return empty results.

#### Filter by date range

```php
$puzzles = $client->puzzle()->collect([
    'puzzleDateFrom' => '2025-11-01',
    'puzzleDateTo' => '2025-11-30'
]);
```

#### Filter by puzzle types

```php
$puzzles = $client->puzzle()->collect([
    'puzzleTypes' => ['XW', 'WS', 'SU']
]);
```

**Available puzzle type abbreviations:**
- `XW` - Crossword
- `XC` - Cryptic Crossword
- `WW` - Word Wheel
- `WS` - Wordsearch
- `SW` - Splitwords
- `SU` - Sudoku (all variants)
- `JS` - Jigsaw
- `HM` - Hangman
- `AU` - Add Up

#### Filter by puzzle names

When filtering by puzzle names, you can specify exact puzzle names. If you want only specific puzzle variants (e.g., only "Easy Sudoku"), do NOT include that puzzle type in `puzzleTypes`, otherwise all puzzles of that type will be returned.

```php
// Get only "Easy Sudoku" puzzles (DO NOT include 'SU' in puzzleTypes)
$puzzles = $client->puzzle()->collect([
    'puzzleNames' => ['Easy Sudoku']
]);

// Get all Crosswords and Wordsearches, plus only "Easy Sudoku"
$puzzles = $client->puzzle()->collect([
    'puzzleTypes' => ['XW', 'WS'],  // Note: 'SU' is NOT included here
    'puzzleNames' => ['Easy Sudoku']
]);
```

#### Combine multiple filters

```php
$puzzles = $client->puzzle()->collect([
    'puzzleDate' => '2025-11-12',
    'puzzleTypes' => ['XW', 'WS'],
    'puzzleNames' => ['Easy Sudoku']
]);
```

#### Practical Examples

**Get today's wordsearches:**
```php
$puzzles = $client->puzzle()->collect([
    'puzzleDate' => date('Y-m-d'),
    'puzzleTypes' => ['WS']
]);
```

**Get all sudoku variants for a date range:**
```php
$puzzles = $client->puzzle()->collect([
    'puzzleDateFrom' => '2025-11-01',
    'puzzleDateTo' => '2025-11-07',
    'puzzleTypes' => ['SU']
]);
```

**Build a weekly puzzle archive:**
```php
// Get all puzzles from last 7 days
$startDate = date('Y-m-d', strtotime('-7 days'));
$endDate = date('Y-m-d');

$puzzles = $client->puzzle()->collect([
    'puzzleDateFrom' => $startDate,
    'puzzleDateTo' => $endDate
]);

foreach ($puzzles as $puzzle) {
    echo "{$puzzle['name']} - {$puzzle['rdate']}\n";
}
```

## Error Handling

The SDK throws specific exceptions for different error types:

```php
use TomGould\PuzzlerPHPSDK\Exception\AuthenticationException;
use TomGould\PuzzlerPHPSDK\Exception\BadRequestException;
use TomGould\PuzzlerPHPSDK\Exception\NotFoundException;
use TomGould\PuzzlerPHPSDK\Exception\MethodNotAllowedException;
use TomGould\PuzzlerPHPSDK\Exception\ServerException;
use TomGould\PuzzlerPHPSDK\Exception\PuzzlerException;

try {
    $puzzles = $client->puzzle()->collect(['puzzleDate' => '2025-11-12']);
} catch (AuthenticationException $e) {
    // Handle authentication errors (401)
    echo "Authentication failed: " . $e->getMessage();
} catch (BadRequestException $e) {
    // Handle bad request errors (400)
    echo "Bad request: " . $e->getMessage();
    echo "Response: " . $e->getResponseBody();
} catch (NotFoundException $e) {
    // Handle not found errors (404)
    echo "Not found: " . $e->getMessage();
} catch (ServerException $e) {
    // Handle server errors (500, 502)
    echo "Server error: " . $e->getMessage();
} catch (PuzzlerException $e) {
    // Handle any other errors
    echo "Error: " . $e->getMessage();

    // Get raw response body if needed
    if ($e->getResponseBody()) {
        echo "Response: " . $e->getResponseBody();
    }
}
```

## API Reference

### PuzzlerClient

Main client class for accessing the API.

#### Constructor

```php
public function __construct(
    string $clientId,
    string $apiKey,
    string $secretKey,
    string $baseUrl = 'https://rest-api.puzzlerdigital.uk'
)
```

#### Methods

- `puzzle(): PuzzleClient` - Get puzzle client instance
- `health(): HealthClient` - Get health client instance

### PuzzleClient

Client for puzzle-related operations.

#### Methods

- `collect(array $filters = []): array` - Collect puzzles with optional filters
- `dictionary(): array` - Get dictionary of available puzzle types and names

#### Collect Filters

| Parameter | Type | Required | Format | Description |
|-----------|------|----------|--------|-------------|
| `puzzleDate` | string | No | YYYY-MM-DD | Exact date to filter by |
| `puzzleDateFrom` | string | No | YYYY-MM-DD | Start date for range filter |
| `puzzleDateTo` | string | No | YYYY-MM-DD | End date for range filter |
| `puzzleTypes` | array | No | Array of strings | Puzzle type abbreviations |
| `puzzleNames` | array | No | Array of strings | Exact puzzle names |

### HealthClient

Client for health check operations.

#### Methods

- `check(): string` - Check API health status

## Exception Hierarchy

```
Exception
└── PuzzlerException
    ├── AuthenticationException (401)
    ├── BadRequestException (400)
    ├── NotFoundException (404)
    ├── MethodNotAllowedException (405)
    └── ServerException (500, 502)
```

## Troubleshooting

### No puzzles returned when filtering by date

**Problem:** You're filtering by a specific date but getting an empty array back, even though puzzles should exist.

**Possible causes:**
1. **Date is outside the bundle range** - The API only returns puzzles from the "latest bundle". If you request a date that's not in the current bundle, you'll get no results.
2. **Date format is incorrect** - Ensure you're using YYYY-MM-DD format (e.g., '2025-11-12', not '11/12/2025')
3. **No puzzles published for that date** - Some dates may not have puzzles available

**Solution:**
```php
// First, check what's available by getting all puzzles
$allPuzzles = $client->puzzle()->collect();

// Check the date range in the bundle
$dates = array_unique(array_column($allPuzzles, 'rdate'));
print_r($dates);

// Then filter by a date you know exists
$puzzles = $client->puzzle()->collect([
    'puzzleDate' => '2025-11-12'  // Use a date from the above list
]);
```

### Building a puzzle archive

**Problem:** You need to archive puzzles from multiple days/weeks/months, but the API only provides the "latest bundle".

**Solution:** You'll need to implement a cron job or scheduled task that regularly fetches and stores puzzles:

```php
// Example: Daily archival script
$client = new PuzzlerClient($clientId, $apiKey, $secretKey);

// Get today's puzzles
$todaysPuzzles = $client->puzzle()->collect([
    'puzzleDate' => date('Y-m-d')
]);

// Store them in your database
foreach ($todaysPuzzles as $puzzle) {
    // Save to database
    $db->insert('puzzle_archive', [
        'puzzle_id' => $puzzle['puzzle_id'],
        'pml_id' => $puzzle['pml_id'],
        'type' => $puzzle['abbr'],
        'name' => $puzzle['name'],
        'date' => $puzzle['rdate'],
        'game_data' => json_encode($puzzle['game_data']),
        'archived_at' => date('Y-m-d H:i:s')
    ]);
}
```

### Authentication errors

**Problem:** Getting 401 Authentication Failed errors.

**Possible causes:**
1. **Incorrect credentials** - Double-check your Client ID, API Key, and Secret Key
2. **Clock skew** - The API signature is time-sensitive (valid for 5 minutes). Ensure your server's clock is synchronized

**Solution:**
```php
// Verify your credentials are correct
$client = new PuzzlerClient(
    'YOUR_CLIENT_ID',
    'YOUR_API_KEY',
    'YOUR_SECRET_KEY'
);

// Test with health check first (public endpoint)
try {
    $health = $client->health()->check();
    echo "Connection successful!\n";
} catch (AuthenticationException $e) {
    echo "Auth failed. Check your credentials.\n";
}

// Check server time
echo "Server time: " . date('Y-m-d H:i:s') . " UTC: " . gmdate('Y-m-d H:i:s') . "\n";
```

### Filter by puzzle name not working

**Problem:** You're filtering by puzzle name but getting all puzzles of that type.

**Cause:** When using `puzzleNames`, you must NOT include the corresponding puzzle type in `puzzleTypes`, otherwise the API returns all puzzles of that type.

**Incorrect:**
```php
// This will return ALL Sudoku puzzles, ignoring the name filter
$puzzles = $client->puzzle()->collect([
    'puzzleTypes' => ['SU'],  // ❌ Don't include 'SU' here
    'puzzleNames' => ['Easy Sudoku']
]);
```

**Correct:**
```php
// This will return ONLY "Easy Sudoku" puzzles
$puzzles = $client->puzzle()->collect([
    'puzzleNames' => ['Easy Sudoku']  // ✅ Omit 'SU' from puzzleTypes
]);

// Or combine with other types:
$puzzles = $client->puzzle()->collect([
    'puzzleTypes' => ['XW', 'WS'],  // ✅ 'SU' is NOT included
    'puzzleNames' => ['Easy Sudoku']
]);
```

### Empty results with valid filters

**Problem:** You're certain your filters should match puzzles, but you're getting an empty array.

**Debug steps:**

```php
// Step 1: Get the dictionary to see what's available
$dictionary = $client->puzzle()->dictionary();
print_r($dictionary);

// Step 2: Get all puzzles to see what's in the bundle
$allPuzzles = $client->puzzle()->collect();
echo "Total puzzles: " . count($allPuzzles) . "\n";

// Step 3: Check a specific puzzle's properties
if (!empty($allPuzzles)) {
    print_r($allPuzzles[0]);
}

// Step 4: Try filtering by something you know exists
$firstPuzzle = $allPuzzles[0];
$filtered = $client->puzzle()->collect([
    'puzzleDate' => $firstPuzzle['rdate'],
    'puzzleTypes' => [$firstPuzzle['abbr']]
]);
echo "Filtered results: " . count($filtered) . "\n";
```

## API Quirks and Technical Notes

### Request Body Format

The Puzzler API has an unusual requirement for the request body format:

- **When filters are provided:** Send them directly in the request body
  ```json
  {
    "puzzleDate": "2025-11-12",
    "puzzleTypes": ["WS"]
  }
  ```

- **When NO filters are provided:** The body must be wrapped in a "model" property
  ```json
  {
    "model": {}
  }
  ```

This SDK handles this automatically, so you don't need to worry about it.

### Date Filtering Behavior

The API operates on the "latest bundle" concept:
- Puzzles are bundled and released periodically
- You can only access puzzles from the current/latest bundle
- Historical puzzles from older bundles are not accessible via the API
- To build an archive, you must fetch and store puzzles regularly

### Signature Validity

Authentication signatures are valid for 5 minutes from generation. The SDK generates a fresh signature for each request, so this shouldn't be an issue unless you have extreme clock skew.

## Testing

### Setup

1. Copy the environment example file:
```bash
cp .env.example .env
```

2. Edit `.env` and add your credentials:
```bash
PUZZLER_CLIENT_ID=your_client_id_here
PUZZLER_API_KEY=your_api_key_here
PUZZLER_SECRET_KEY=your_secret_key_here
PUZZLER_BASE_URL=https://rest-api.puzzlerdigital.uk
```

3. Load environment variables:
```bash
export $(cat .env | xargs)
```

### Run Tests

Run all tests:
```bash
./vendor/bin/phpunit
```

Run only unit tests (no credentials required):
```bash
./vendor/bin/phpunit --testsuite Unit
```

Run only integration tests (credentials required):
```bash
./vendor/bin/phpunit --testsuite Integration
```

Test your credentials specifically:
```bash
./vendor/bin/phpunit tests/Integration/CredentialsTest.php
```

Run with coverage:
```bash
./vendor/bin/phpunit --coverage-html coverage
```

### Test Structure

```
tests/
├── Unit/                           # Unit tests (no API calls)
│   ├── PuzzlerClientTest.php
│   ├── Http/HttpClientTest.php
│   └── Exception/PuzzlerExceptionTest.php
└── Integration/                    # Integration tests (require credentials)
    ├── CredentialsTest.php        # Test your API credentials
    ├── HealthCheckTest.php        # Test health endpoint
    ├── PuzzleDictionaryTest.php   # Test dictionary endpoint
    └── PuzzleCollectTest.php      # Test puzzle collection
```

## License

MIT License - see LICENSE file for details

## Author

**Tom Gould**
- GitHub: [@tomgould](https://github.com/tomgould)

## Repository

https://github.com/tomgould/PuzzlerPHPSDK

## Support

For issues, questions, or contributions, please visit the [GitHub repository](https://github.com/tomgould/PuzzlerPHPSDK/issues).

