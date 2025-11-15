<?php
/**
 * Example: CJ Affiliate Promotional Properties API
 * 
 * This example demonstrates how to manage PIDs (Publisher IDs)
 */

require_once __DIR__ . '/../autoload.php';

use CJAffiliate\CJClient;
use CJAffiliate\Exceptions\CJException;

$config = [
    'access_token' => 'YOUR_PERSONAL_ACCESS_TOKEN',
    'publisher_id' => 'YOUR_PUBLISHER_ID'
];

try {
    $client = new CJClient($config);
    
    echo "=== CJ Affiliate Promotional Properties API ===\n\n";
    
    // Example 1: List all promotional properties
    echo "1. Listing promotional properties:\n";
    echo str_repeat('-', 50) . "\n";
    
    $properties = $client->promotionalProperties()->list();
    
    if (isset($properties['data']['promotionalProperties']['resultList'])) {
        $props = $properties['data']['promotionalProperties']['resultList'];
        echo "Total properties: " . count($props) . "\n\n";
        
        foreach ($props as $prop) {
            echo "PID: {$prop['pid']}\n";
            echo "  Name: {$prop['name']}\n";
            echo "  Description: {$prop['description']}\n";
            echo "  Website: {$prop['websiteUrl']}\n";
            echo "  Status: {$prop['status']}\n";
            echo "  Created: {$prop['createdDate']}\n\n";
        }
    }
    
    // Example 2: Create new promotional property
    echo "\n2. Creating new promotional property:\n";
    echo str_repeat('-', 50) . "\n";
    
    $newProperty = $client->promotionalProperties()->create([
        'name' => 'My New Campaign',
        'description' => 'Campaign for summer sales',
        'website_url' => 'https://example.com/summer'
    ]);
    
    if (isset($newProperty['data']['createPromotionalProperty'])) {
        $created = $newProperty['data']['createPromotionalProperty'];
        echo "Property created successfully!\n";
        echo "  PID: {$created['pid']}\n";
        echo "  Name: {$created['name']}\n";
        echo "  Status: {$created['status']}\n";
    }
    
    echo "\n=== Done! ===\n";
    
} catch (CJException $e) {
    echo "\nCJ Affiliate Error:\n";
    echo $e->getDetails();
} catch (\Exception $e) {
    echo "\nError: " . $e->getMessage() . "\n";
}
