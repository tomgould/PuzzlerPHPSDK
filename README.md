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
    'https://rest-api-stage.puzzlerdigital.uk' // Optional: Base URL
);
```

### Health Check

```php
$health = $client->health()->check();
```

### Get Puzzle Dictionary

```php
$dictionary = $client->puzzle()->dictionary();

// Available puzzle types
print_r($dictionary['types']); // ['XW', 'SU', 'WS', ...]

// Available puzzle names by type
print_r($dictionary['names']); // ['SU' => ['Sudoku'], ...]
```

### Collect Puzzles

#### Get all puzzles

```php
$puzzles = $client->puzzle()->collect();
```

#### Filter by specific date

```php
$puzzles = $client->puzzle()->collect([
    'puzzleDate' => '2025-01-23'
]);
```

#### Filter by date range

```php
$puzzles = $client->puzzle()->collect([
    'puzzleDateFrom' => '2025-01-01',
    'puzzleDateTo' => '2025-01-31'
]);
```

#### Filter by puzzle types

```php
$puzzles = $client->puzzle()->collect([
    'puzzleTypes' => ['XW', 'WS', 'SU']
]);
```

#### Filter by puzzle names

```php
$puzzles = $client->puzzle()->collect([
    'puzzleNames' => ['Easy Sudoku', 'Hard Sudoku']
]);
```

#### Combine multiple filters

```php
$puzzles = $client->puzzle()->collect([
    'puzzleDate' => '2025-01-23',
    'puzzleTypes' => ['XW', 'WS'],
    'puzzleNames' => ['Easy Sudoku']
]);
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
    $puzzles = $client->puzzle()->collect(['puzzleDate' => '2025-01-23']);
} catch (AuthenticationException $e) {
    // Handle authentication errors (401)
    echo "Authentication failed: " . $e->getMessage();
} catch (BadRequestException $e) {
    // Handle bad request errors (400)
    echo "Bad request: " . $e->getMessage();
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
    string $baseUrl = 'https://rest-api-stage.puzzlerdigital.uk'
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

## License

MIT License - see LICENSE file for details

## Author

**Tom Gould**
- GitHub: [@tomgould](https://github.com/tomgould)

## Repository

https://github.com/tomgould/PuzzlerPHPSDK

## Support

For issues, questions, or contributions, please visit the [GitHub repository](https://github.com/tomgould/PuzzlerPHPSDK/issues).

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
PUZZLER_BASE_URL=https://rest-api-stage.puzzlerdigital.uk
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