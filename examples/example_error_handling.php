<?php
/**
 * Example: Error Handling in CJ Affiliate SDK
 * 
 * This example demonstrates how to properly handle errors
 * 
 * Installation:
 * composer require luizsilva-dev/cj-sdk-php
 */

// If installed via Composer
// require_once __DIR__ . '/../vendor/autoload.php';

// If using manual installation
require_once __DIR__ . '/../autoload.php';

use CJAffiliate\CJClient;
use CJAffiliate\Exceptions\CJException;

echo "=== CJ Affiliate SDK - Error Handling Examples ===\n\n";

// Configuration
$config = [
    'access_token' => 'YOUR_PERSONAL_ACCESS_TOKEN',
    'publisher_id' => 'YOUR_PUBLISHER_ID',
    'debug' => true // Enable debug for more details
];

// Example 1: Invalid credentials
echo "1. Testing with invalid credentials:\n";
echo str_repeat('-', 50) . "\n";

try {
    $client = new CJClient([
        'access_token' => 'invalid_token',
        'publisher_id' => 'invalid_id'
    ]);
    
    $products = $client->products()->search([
        'website_id' => 'invalid_website',
        'keywords' => 'test'
    ]);
    
} catch (CJException $e) {
    echo "✓ Caught CJException:\n";
    echo "  Message: {$e->getMessage()}\n";
    echo "  Code: {$e->getCode()}\n";
    
    // Get response details
    $response = $e->getResponse();
    if ($response) {
        echo "  Response details:\n";
        if (isset($response['raw_response'])) {
            echo "    Raw: " . substr($response['raw_response'], 0, 100) . "...\n";
        }
        if (isset($response['http_code'])) {
            echo "    HTTP Code: {$response['http_code']}\n";
        }
        if (isset($response['content_type'])) {
            echo "    Content-Type: {$response['content_type']}\n";
        }
    }
    echo "\n";
}

// Example 2: Missing required parameters
echo "\n2. Testing with missing required parameters:\n";
echo str_repeat('-', 50) . "\n";

try {
    $client = new CJClient($config);
    
    // Missing website_id (required)
    $products = $client->products()->search([
        'keywords' => 'laptop'
    ]);
    
} catch (CJException $e) {
    echo "✓ Caught CJException:\n";
    echo "  Message: {$e->getMessage()}\n";
    echo "  Code: {$e->getCode()}\n\n";
}

// Example 3: Network timeout
echo "\n3. Handling timeout errors:\n";
echo str_repeat('-', 50) . "\n";

try {
    $client = new CJClient([
        'access_token' => $config['access_token'],
        'publisher_id' => $config['publisher_id'],
        'timeout' => 1 // Very short timeout
    ]);
    
    $products = $client->products()->search([
        'website_id' => 'YOUR_WEBSITE_ID',
        'keywords' => 'laptop'
    ]);
    
} catch (CJException $e) {
    echo "✓ Caught timeout error:\n";
    echo "  Message: {$e->getMessage()}\n\n";
}

// Example 4: Proper error handling in production
echo "\n4. Production-ready error handling:\n";
echo str_repeat('-', 50) . "\n";

function searchProducts($client, $websiteId, $keywords) {
    try {
        $products = $client->products()->search([
            'website_id' => $websiteId,
            'keywords' => $keywords,
            'records_per_page' => 10
        ]);
        
        return [
            'success' => true,
            'data' => $products,
            'error' => null
        ];
        
    } catch (CJException $e) {
        // Log error for debugging
        error_log("CJ API Error: " . $e->getMessage());
        error_log("Details: " . json_encode($e->getResponse()));
        
        // Return user-friendly error
        return [
            'success' => false,
            'data' => null,
            'error' => 'Unable to fetch products. Please try again later.',
            'details' => $e->getMessage(),
            'code' => $e->getCode()
        ];
    }
}

// Test the function
$result = searchProducts(new CJClient($config), 'YOUR_WEBSITE_ID', 'laptop');

if ($result['success']) {
    echo "✓ Products fetched successfully\n";
    echo "  Found: {$result['data']['total_matched']} products\n";
} else {
    echo "✗ Error occurred\n";
    echo "  User message: {$result['error']}\n";
    echo "  Technical details: {$result['details']}\n";
    echo "  Error code: {$result['code']}\n";
}

echo "\n";

// Example 5: Retry logic for transient errors
echo "\n5. Implementing retry logic:\n";
echo str_repeat('-', 50) . "\n";

function searchProductsWithRetry($client, $websiteId, $keywords, $maxRetries = 3) {
    $attempt = 0;
    
    while ($attempt < $maxRetries) {
        try {
            $products = $client->products()->search([
                'website_id' => $websiteId,
                'keywords' => $keywords
            ]);
            
            return ['success' => true, 'data' => $products];
            
        } catch (CJException $e) {
            $attempt++;
            
            // Check if error is retryable (5xx errors, timeouts)
            $isRetryable = $e->getCode() >= 500 || 
                          strpos($e->getMessage(), 'timeout') !== false ||
                          strpos($e->getMessage(), 'cURL') !== false;
            
            if ($isRetryable && $attempt < $maxRetries) {
                echo "  Attempt {$attempt} failed, retrying in 2 seconds...\n";
                sleep(2);
                continue;
            }
            
            // Non-retryable error or max retries reached
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'attempts' => $attempt
            ];
        }
    }
}

$result = searchProductsWithRetry(new CJClient($config), 'YOUR_WEBSITE_ID', 'laptop');

if ($result['success']) {
    echo "✓ Products fetched successfully\n";
} else {
    echo "✗ Failed after {$result['attempts']} attempts\n";
    echo "  Error: {$result['error']}\n";
}

echo "\n=== Error Handling Examples Complete ===\n";
echo "\nBest Practices:\n";
echo "1. Always use try-catch blocks\n";
echo "2. Log errors for debugging\n";
echo "3. Show user-friendly messages\n";
echo "4. Implement retry logic for transient errors\n";
echo "5. Check error codes to determine if retry is appropriate\n";
echo "6. Enable debug mode during development\n";
