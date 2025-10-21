<?php
require_once __DIR__ . '/../vendor/autoload.php';

$clientId = getenv('PUZZLER_CLIENT_ID');
$apiKey = getenv('PUZZLER_API_KEY');
$secretKey = getenv('PUZZLER_SECRET_KEY');

$timestamp = time();
$message = $apiKey . '_' . $clientId . '_' . $timestamp . '_' . $secretKey;

echo "=== DEBUG INFO ===\n";
echo "Client ID: " . $clientId . "\n";
echo "API Key: " . $apiKey . "\n";
echo "Secret Key: " . $secretKey . "\n";
echo "Timestamp: " . $timestamp . "\n";
echo "Message: " . $message . "\n";
echo "SHA256: " . hash('sha256', $message) . "\n";
echo "Signature: " . base64_encode(hash('sha256', $message, true)) . "\n\n";

echo "Headers would be:\n";
echo "X-Signature: " . base64_encode(hash('sha256', $message, true)) . "\n";
echo "X-Timestamp: " . $timestamp . "\n";
echo "X-Client-Id: " . $clientId . "\n";
echo "X-Api-Key: " . $apiKey . "\n";