<?php

namespace TomGould\PuzzlerPHPSDK\Exception;

use Exception;

/**
 * Base exception for Puzzler API
 *
 * @package TomGould\PuzzlerPHPSDK\Exception
 */
class PuzzlerException extends Exception
{
    /**
     * @var string|null
     */
    protected $responseBody;

    /**
     * Create a new exception
     *
     * @param string $message Exception message
     * @param int $code HTTP status code
     * @param string|null $responseBody Raw response body
     */
    public function __construct($message = '', $code = 0, $responseBody = null)
    {
        parent::__construct($message, $code);
        $this->responseBody = $responseBody;
    }

    /**
     * Get the raw response body
     *
     * @return string|null
     */
    public function getResponseBody()
    {
        return $this->responseBody;
    }
}

