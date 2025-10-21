<?php

namespace TomGould\PuzzlerPHPSDK\Http;

use TomGould\PuzzlerPHPSDK\Exception\AuthenticationException;
use TomGould\PuzzlerPHPSDK\Exception\BadRequestException;
use TomGould\PuzzlerPHPSDK\Exception\MethodNotAllowedException;
use TomGould\PuzzlerPHPSDK\Exception\NotFoundException;
use TomGould\PuzzlerPHPSDK\Exception\PuzzlerException;
use TomGould\PuzzlerPHPSDK\Exception\ServerException;

/**
 * HTTP Client for Puzzler API
 *
 * Handles all HTTP communication with the Puzzler API including
 * authentication, request signing, and response handling.
 *
 * @package TomGould\PuzzlerPHPSDK\Http
 */
class HttpClient
{
    /**
     * @var string
     */
    private $clientId;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var string
     */
    private $secretKey;

    /**
     * @var string
     */
    private $baseUrl;

    /**
     * Create a new HTTP client
     *
     * @param string $clientId X-Client-Id credential
     * @param string $apiKey X-Api-Key credential
     * @param string $secretKey Secret key for signature generation
     * @param string $baseUrl Base URL for the API
     */
    public function __construct($clientId, $apiKey, $secretKey, $baseUrl)
    {
        $this->clientId = $clientId;
        $this->apiKey = $apiKey;
        $this->secretKey = $secretKey;
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    /**
     * Generate authentication signature using SHA256 and Base64
     *
     * @param int $timestamp Unix timestamp
     * @return string Base64 encoded signature
     */
    private function generateSignature($timestamp)
    {
        $message = $this->apiKey . '_' . $this->clientId . '_' . $timestamp . '_' . $this->secretKey;
        $hash = hash('sha256', $message, true);
        return base64_encode($hash);
    }

    /**
     * Build HTTP request headers with authentication
     *
     * @param int $timestamp Unix timestamp
     * @return array Array of header strings
     */
    private function buildHeaders($timestamp)
    {
        return [
            'X-Signature: ' . $this->generateSignature($timestamp),
            'X-Timestamp: ' . (string)$timestamp,
            'X-Client-Id: ' . (string)$this->clientId,
            'X-Api-Key: ' . $this->apiKey,
            'Content-Type: application/json',
        ];
    }

    /**
     * Execute an HTTP request
     *
     * @param string $method HTTP method (GET, POST, etc.)
     * @param string $endpoint API endpoint path
     * @param array|null $body Request body data
     * @return mixed Response data
     * @throws PuzzlerException If request fails
     */
    public function request($method, $endpoint, $body = null)
    {
        $url = $this->baseUrl . $endpoint;
        $timestamp = time();
        $headers = $this->buildHeaders($timestamp);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        if ($body !== null) {
            $jsonBody = json_encode($body);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonBody);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $error = curl_error($ch);

        curl_close($ch);

        if ($error) {
            throw new PuzzlerException('cURL error: ' . $error);
        }

        return $this->handleResponse($response, $httpCode, $contentType);
    }

    /**
     * Handle API response and throw appropriate exceptions
     *
     * @param string $response Raw response body
     * @param int $httpCode HTTP status code
     * @param string $contentType Response content type
     * @return mixed Parsed response data
     * @throws PuzzlerException If response indicates an error
     */
    private function handleResponse($response, $httpCode, $contentType)
    {
        if ($httpCode === 200) {
            if (strpos($contentType, 'application/json') !== false) {
                $decoded = json_decode($response, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new PuzzlerException('Failed to decode JSON response: ' . json_last_error_msg());
                }
                return $decoded;
            }
            return $response;
        }

        switch ($httpCode) {
            case 400:
                throw new BadRequestException('Bad request', $httpCode, $response);
            case 401:
                throw new AuthenticationException('Authentication failed', $httpCode, $response);
            case 404:
                throw new NotFoundException('Resource not found', $httpCode, $response);
            case 405:
                throw new MethodNotAllowedException('Method not allowed', $httpCode, $response);
            case 500:
            case 502:
                throw new ServerException('Server error', $httpCode, $response);
            default:
                throw new PuzzlerException('Unexpected response', $httpCode, $response);
        }
    }

    /**
     * Execute a GET request
     *
     * @param string $endpoint API endpoint path
     * @return mixed Response data
     * @throws PuzzlerException If request fails
     */
    public function get($endpoint)
    {
        return $this->request('GET', $endpoint);
    }

    /**
     * Execute a POST request
     *
     * @param string $endpoint API endpoint path
     * @param array $body Request body data
     * @return mixed Response data
     * @throws PuzzlerException If request fails
     */
    public function post($endpoint, $body = [])
    {
        return $this->request('POST', $endpoint, $body);
    }
}

