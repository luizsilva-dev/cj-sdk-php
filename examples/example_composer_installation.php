<?php
/**
 * Example: Using CJ Affiliate SDK after Composer Installation
 * 
 * This example shows how to use the SDK after installing it via Composer:
 * composer require luizsilva-dev/cj-sdk-php
 */

// Composer autoload
require_once __DIR__ . '/../vendor/autoload.php';

use CJAffiliate\CJClient;
use CJAffiliate\Exceptions\CJException;

// Configuration
$config = [
    'access_token' => 'YOUR_PERSONAL_ACCESS_TOKEN',
    'publisher_id' => 'YOUR_PUBLISHER_ID',
    'cache_enabled' => true,
    'cache_ttl' => 3600
];

try {
    // Initialize client
    $client = new CJClient($config);
    
    echo "✓ CJ Affiliate SDK loaded successfully via Composer!\n\n";
    
    // Example 1: Search products
    echo "Example 1: Searching for products...\n";
    echo str_repeat('-', 50) . "\n";
    
    $products = $client->products()->search([
        'website_id' => 'YOUR_WEBSITE_ID',
        'keywords' => 'electronics',
        'records_per_page' => 5
    ]);
    
    echo "Found {$products['total_matched']} products\n\n";
    
    foreach (array_slice($products['products'], 0, 3) as $product) {
        echo "Product: {$product['name']}\n";
        echo "  Price: {$product['currency']} {$product['price']}\n";
        echo "  Merchant: {$product['advertiser_name']}\n\n";
    }
    
    // Example 2: Get affiliate links
    echo "\nExample 2: Getting affiliate links...\n";
    echo str_repeat('-', 50) . "\n";
    
    $links = $client->links()->search([
        'website-id' => 'YOUR_WEBSITE_ID',
        'relationship-status' => 'joined',
        'records-per-page' => 3
    ]);
    
    echo "Found {$links['total_matched']} links\n\n";
    
    foreach ($links['links'] as $link) {
        echo "Link: {$link['link_name']}\n";
        echo "  Advertiser: {$link['advertiser_name']}\n";
        echo "  Type: {$link['link_type']}\n\n";
    }
    
    // Example 3: Get commission summary
    echo "\nExample 3: Commission summary...\n";
    echo str_repeat('-', 50) . "\n";
    
    $summary = $client->commissions()->getSummary([
        'start_date' => date('Y-m-01'),
        'end_date' => date('Y-m-d')
    ]);
    
    echo "Period: {$summary['period']['start']} to {$summary['period']['end']}\n";
    echo "Total Commissions: {$summary['total_commissions']}\n";
    echo "Total Amount: \${$summary['total_amount']}\n\n";
    
    echo "\n✓ All examples completed successfully!\n";
    
} catch (CJException $e) {
    echo "CJ Affiliate Error:\n";
    echo $e->getMessage() . "\n";
    echo "Code: " . $e->getCode() . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
