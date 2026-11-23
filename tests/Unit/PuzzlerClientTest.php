<?php

namespace TomGould\PuzzlerPHPSDK\Tests\Unit;

use PHPUnit\Framework\TestCase;
use TomGould\PuzzlerPHPSDK\PuzzlerClient;
use TomGould\PuzzlerPHPSDK\Client\PuzzleClient;
use TomGould\PuzzlerPHPSDK\Client\HealthClient;

/**
 * Unit tests for PuzzlerClient
 */
class PuzzlerClientTest extends TestCase
{
    public function testClientCanBeInstantiated()
    {
        $client = new PuzzlerClient('client123', 'key123', 'secret123');

        $this->assertInstanceOf(PuzzlerClient::class, $client);
    }

    public function testPuzzleClientReturnsCorrectInstance()
    {
        $client = new PuzzlerClient('client123', 'key123', 'secret123');

        $puzzleClient = $client->puzzle();

        $this->assertInstanceOf(PuzzleClient::class, $puzzleClient);
    }

    public function testHealthClientReturnsCorrectInstance()
    {
        $client = new PuzzlerClient('client123', 'key123', 'secret123');

        $healthClient = $client->health();

        $this->assertInstanceOf(HealthClient::class, $healthClient);
    }

    public function testPuzzleClientReturnsSameInstance()
    {
        $client = new PuzzlerClient('client123', 'key123', 'secret123');

        $puzzleClient1 = $client->puzzle();
        $puzzleClient2 = $client->puzzle();

        $this->assertSame($puzzleClient1, $puzzleClient2);
    }

    public function testHealthClientReturnsSameInstance()
    {
        $client = new PuzzlerClient('client123', 'key123', 'secret123');

        $healthClient1 = $client->health();
        $healthClient2 = $client->health();

        $this->assertSame($healthClient1, $healthClient2);
    }

    public function testClientAcceptsCustomBaseUrl()
    {
        $customUrl = 'https://custom-api.example.com';
        $client = new PuzzlerClient('client123', 'key123', 'secret123', $customUrl);

        $this->assertInstanceOf(PuzzlerClient::class, $client);
    }
}

