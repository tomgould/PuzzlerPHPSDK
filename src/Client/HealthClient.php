<?php

namespace TomGould\PuzzlerPHPSDK\Client;

use TomGould\PuzzlerPHPSDK\Exception\PuzzlerException;
use TomGould\PuzzlerPHPSDK\Http\HttpClient;

/**
 * Health Client
 *
 * Handles health check operations for the Puzzler API.
 *
 * @package TomGould\PuzzlerPHPSDK\Client
 */
class HealthClient
{
    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * Create a new Health client
     *
     * @param HttpClient $httpClient HTTP client instance
     */
    public function __construct(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * Check API health status
     *
     * @return string Health status message
     * @throws PuzzlerException If request fails
     */
    public function check()
    {
        return $this->httpClient->get('/api/Health/check');
    }
}

