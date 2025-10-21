<?php

require_once __DIR__ . '/../vendor/autoload.php';

$clientId = getenv('PUZZLER_CLIENT_ID');
$apiKey = getenv('PUZZLER_API_KEY');
$secretKey = getenv('PUZZLER_SECRET_KEY');

if (empty($clientId) || empty($apiKey) || empty($secretKey)) {
    die("Please set environment variables: PUZZLER_CLIENT_ID, PUZZLER_API_KEY, PUZZLER_SECRET_KEY\n");
}

$timestamp = time();
$message = $apiKey . '_' . $clientId . '_' . $timestamp . '_' . $secretKey;
$signature = base64_encode(hash('sha256', $message, true));

echo "=== Equivalent cURL command ===\n\n";
echo "curl -X GET 'https://rest-api.puzzlerdigital.uk/api/Puzzle/dictionary' \\\n";
echo "  -H 'X-Signature: " . $signature . "' \\\n";
echo "  -H 'X-Timestamp: " . $timestamp . "' \\\n";
echo "  -H 'X-Client-Id: " . $clientId . "' \\\n";
echo "  -H 'X-Api-Key: " . $apiKey . "' \\\n";
echo "  -H 'Content-Type: application/json' \\\n";
echo "  -v\n\n";

echo "=== Debug Info ===\n";
echo "Message: " . $message . "\n";
echo "SHA256:  " . hash('sha256', $message) . "\n";
echo "Signature: " . $signature . "\n";
