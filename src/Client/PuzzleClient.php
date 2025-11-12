<?php

namespace TomGould\PuzzlerPHPSDK\Client;

use TomGould\PuzzlerPHPSDK\Exception\PuzzlerException;
use TomGould\PuzzlerPHPSDK\Http\HttpClient;

/**
 * Puzzle Client
 *
 * Handles all puzzle-related API operations including collecting puzzles
 * with filters and retrieving available puzzle dictionaries.
 *
 * @package TomGould\PuzzlerPHPSDK\Client
 */
class PuzzleClient
{
    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * Create a new Puzzle client
     *
     * @param HttpClient $httpClient HTTP client instance
     */
    public function __construct(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * Collect puzzles from the latest bundle with optional filters
     *
     * @param array $filters Optional filters:
     *   - puzzleDate: string (YYYY-MM-DD format) - Exact date
     *   - puzzleDateFrom: string (YYYY-MM-DD format) - Start date range
     *   - puzzleDateTo: string (YYYY-MM-DD format) - End date range
     *   - puzzleTypes: array of strings - Puzzle type abbreviations (e.g., ['XW', 'SU'])
     *   - puzzleNames: array of strings - Puzzle names (e.g., ['Easy Sudoku'])
     * @return array Array of puzzle objects
     * @throws PuzzlerException If request fails
     */
    public function collect(array $filters = [])
    {
        $model = [];

        if (isset($filters["puzzleDate"])) {
            $model["puzzleDate"] = $filters["puzzleDate"];
        }

        if (isset($filters["puzzleDateFrom"])) {
            $model["puzzleDateFrom"] = $filters["puzzleDateFrom"];
        }

        if (isset($filters["puzzleDateTo"])) {
            $model["puzzleDateTo"] = $filters["puzzleDateTo"];
        }

        if (
            isset($filters["puzzleTypes"]) &&
            is_array($filters["puzzleTypes"])
        ) {
            $model["puzzleTypes"] = $filters["puzzleTypes"];
        }

        if (
            isset($filters["puzzleNames"]) &&
            is_array($filters["puzzleNames"])
        ) {
            $model["puzzleNames"] = $filters["puzzleNames"];
        }

        // Wrap the model in a "model" property as required by the .NET API
        $body = ["model" => $model];

        return $this->httpClient->post("/api/Puzzle/collect", $body);
    }

    /**
     * Get dictionary of available puzzle types and names
     *
     * Returns a dictionary containing all available puzzle types and their
     * associated names from the latest bundle.
     *
     * @return array Dictionary with structure:
     *   - types: array of puzzle type abbreviations
     *   - names: associative array mapping types to arrays of names
     * @throws PuzzlerException If request fails
     */
    public function dictionary()
    {
        return $this->httpClient->get("/api/Puzzle/dictionary");
    }
}
