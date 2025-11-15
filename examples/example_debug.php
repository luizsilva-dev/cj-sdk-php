<?php
/**
 * Example: Debug and Troubleshoot CJ Affiliate SDK
 * 
 * Use this script to diagnose connection issues
 */

require_once __DIR__ . '/../autoload.php';

use CJAffiliate\CJClient;
use CJAffiliate\Exceptions\CJException;

echo "=== CJ Affiliate SDK - Debug Mode ===\n\n";

// Configuration
$config = [
    'access_token' => 'YOUR_PERSONAL_ACCESS_TOKEN',
    'publisher_id' => 'YOUR_PUBLISHER_ID',
    'debug' => true // Enable debug
];

// Step 1: Check configuration
echo "Step 1: Checking configuration...\n";
echo str_repeat('-', 50) . "\n";

if ($config['access_token'] === 'YOUR_PERSONAL_ACCESS_TOKEN') {
    echo "❌ ERROR: You need to set your actual credentials!\n\n";
    echo "Please edit this file and replace:\n";
    echo "  - YOUR_PERSONAL_ACCESS_TOKEN with your actual token\n";
    echo "  - YOUR_PUBLISHER_ID with your actual ID\n\n";
    echo "Get your credentials from:\n";
    echo "  https://developers.cj.com/\n\n";
    exit(1);
}

// Validate token format
$token = $config['access_token'];
$tokenLength = strlen($token);

echo "✓ Access token length: {$tokenLength} characters\n";
echo "✓ Access token preview: " . substr($token, 0, 10) . "..." . substr($token, -5) . "\n";
echo "✓ Publisher ID: {$config['publisher_id']}\n";

// Check for common token issues
$warnings = [];
if ($tokenLength < 20) {
    $warnings[] = "Token seems too short (may be incomplete)";
}
if (preg_match('/\s/', $token)) {
    $warnings[] = "Token contains whitespace (should be removed)";
}
if (preg_match('/[^a-zA-Z0-9\-_.]/', $token)) {
    $warnings[] = "Token contains unusual characters";
}

if (!empty($warnings)) {
    echo "\n⚠️  Token validation warnings:\n";
    foreach ($warnings as $warning) {
        echo "   - {$warning}\n";
    }
}
echo "\n";

// Step 2: Initialize client
echo "Step 2: Initializing CJ client...\n";
echo str_repeat('-', 50) . "\n";

try {
    $client = new CJClient($config);
    echo "✓ Client initialized successfully\n\n";
} catch (CJException $e) {
    echo "❌ Failed to initialize client:\n";
    echo "   {$e->getMessage()}\n\n";
    exit(1);
}

// Step 3: Test advertiser lookup (simplest endpoint)
echo "Step 3: Testing Advertiser Lookup API...\n";
echo str_repeat('-', 50) . "\n";

try {
    $advertisers = $client->advertisers()->search([
        'records-per-page' => 5
    ]);
    
    $count = $advertisers['total_matched'] ?? 0;
    echo "✓ Advertiser API works! Found {$count} advertisers\n";
    
    if ($count > 0) {
        echo "\nFirst advertiser:\n";
        $first = $advertisers['advertisers'][0] ?? null;
        if ($first) {
            echo "  Name: {$first['advertiser_name']}\n";
            echo "  ID: {$first['advertiser_id']}\n";
            echo "  Status: {$first['relationship_status']}\n";
        }
    }
    echo "\n";
    
} catch (CJException $e) {
    echo "❌ Advertiser API failed:\n";
    echo "   Message: {$e->getMessage()}\n";
    echo "   Code: {$e->getCode()}\n";
    
    $response = $e->getResponse();
    if ($response) {
        echo "\n   Response details:\n";
        foreach ($response as $key => $value) {
            if (is_array($value)) {
                echo "     {$key}: " . json_encode($value) . "\n";
            } else {
                echo "     {$key}: {$value}\n";
            }
        }
    }
    echo "\n";
}

// Step 4: Test product search
echo "Step 4: Testing Product Search API...\n";
echo str_repeat('-', 50) . "\n";

$websiteId = readline("Enter your Website ID (from CJ Members → Account → Websites): ");

if (empty($websiteId)) {
    echo "⚠️  Skipping product search (no Website ID provided)\n\n";
} else {
    try {
        $products = $client->products()->search([
            'website_id' => $websiteId,
            'keywords' => 'laptop',
            'records_per_page' => 3
        ]);
        
        $count = $products['total_matched'] ?? 0;
        echo "✓ Product API works! Found {$count} products\n";
        
        if ($count > 0 && !empty($products['products'])) {
            echo "\nFirst product:\n";
            $first = $products['products'][0] ?? null;
            if ($first) {
                echo "  Name: {$first['name']}\n";
                echo "  Price: {$first['currency']} {$first['price']}\n";
                echo "  Merchant: {$first['advertiser_name']}\n";
            }
        }
        echo "\n";
        
    } catch (CJException $e) {
        echo "❌ Product API failed:\n";
        echo "   Message: {$e->getMessage()}\n";
        echo "   Code: {$e->getCode()}\n";
        
        $response = $e->getResponse();
        if ($response) {
            echo "\n   Response details:\n";
            foreach ($response as $key => $value) {
                if (is_array($value)) {
                    echo "     {$key}: " . json_encode($value) . "\n";
                } else {
                    $valueStr = is_string($value) ? substr($value, 0, 200) : $value;
                    echo "     {$key}: {$valueStr}\n";
                }
            }
        }
        echo "\n";
    }
}

// Step 5: Summary
echo "=== Debug Summary ===\n";
echo str_repeat('-', 50) . "\n";
echo "If you're getting errors:\n\n";
echo "1. 'Empty response from API':\n";
echo "   - Verify your access token is correct\n";
echo "   - Check if your Publisher account is active\n";
echo "   - Make sure you've joined at least one advertiser program\n\n";
echo "2. HTTP 401 Unauthorized:\n";
echo "   - Your access token is invalid or expired\n";
echo "   - Generate a new token at https://developers.cj.com/\n\n";
echo "3. HTTP 403 Forbidden:\n";
echo "   - Your account may not have API access\n";
echo "   - Contact CJ Affiliate support\n\n";
echo "4. 'Parameter website_id is required':\n";
echo "   - You need to register a website in CJ Members portal\n";
echo "   - Go to Account → Websites\n\n";

echo "\nFor more help:\n";
echo "  - CJ Developer Portal: https://developers.cj.com/\n";
echo "  - SDK Issues: https://github.com/luizsilva-dev/cj-sdk-php/issues\n";
