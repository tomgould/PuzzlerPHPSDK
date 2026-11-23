<?php

namespace TomGould\PuzzlerPHPSDK;

use TomGould\PuzzlerPHPSDK\Client\HealthClient;
use TomGould\PuzzlerPHPSDK\Client\PuzzleClient;
use TomGould\PuzzlerPHPSDK\Http\HttpClient;

/**
 * Main Puzzler API Client
 *
 * @package TomGould\PuzzlerPHPSDK
 */
class PuzzlerClient
{
    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * @var PuzzleClient|null
     */
    private $puzzleClient;

    /**
     * @var HealthClient|null
     */
    private $healthClient;

    /**
     * Create a new Puzzler API client
     *
     * @param string $clientId X-Client-Id credential
     * @param string $apiKey X-Api-Key credential
     * @param string $secretKey Secret key for signature generation
     * @param string $baseUrl Base URL for the API (default: production environment)
     */
    public function __construct($clientId, $apiKey, $secretKey, $baseUrl = 'https://rest-api.puzzlerdigital.uk')
    {
        $this->httpClient = new HttpClient($clientId, $apiKey, $secretKey, $baseUrl);
    }

    /**
     * Get Puzzle client for puzzle-related operations
     *
     * @return PuzzleClient
     */
    public function puzzle()
    {
        if ($this->puzzleClient === null) {
            $this->puzzleClient = new PuzzleClient($this->httpClient);
        }
        return $this->puzzleClient;
    }

    /**
     * Get Health client for health check operations
     *
     * @return HealthClient
     */
    public function health()
    {
        if ($this->healthClient === null) {
            $this->healthClient = new HealthClient($this->httpClient);
        }
        return $this->healthClient;
    }
}

