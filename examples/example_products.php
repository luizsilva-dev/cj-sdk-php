<?php
/**
 * Example: CJ Affiliate Link Search (Product Information)
 * 
 * This example demonstrates how to search for affiliate links that contain product information.
 * Note: CJ does not offer a dedicated Product Search API. The Link Search API returns
 * affiliate links which include product details (names, URLs, commissions, etc.)
 */

require_once __DIR__ . '/../autoload.php';

use CJAffiliate\CJClient;
use CJAffiliate\Exceptions\CJException;

// Configuration (replace with your credentials)
$config = [
    'access_token' => 'YOUR_PERSONAL_ACCESS_TOKEN',
    'publisher_id' => 'YOUR_PUBLISHER_ID',
    'cache_enabled' => true,
    'cache_ttl' => 3600,
    'debug' => false
];

try {
    // Initialize client
    $client = new CJClient($config);
    
    echo "=== CJ Affiliate Link Search (Product Information) ===\n\n";
    
    // Example 1: Simple keyword search
    echo "1. Searching for links with keyword 'knee brace':\n";
    echo str_repeat('-', 70) . "\n";
    
    $links = $client->products()->search([
        'website_id' => 'YOUR_WEBSITE_ID', // REQUIRED
        'keywords' => 'knee brace',
        'records_per_page' => 5,
        'page_number' => 1
    ]);
    
    echo "Total links found: {$links['total_matched']}\n";
    echo "Links returned on this page: {$links['records_returned']}\n\n";
    
    foreach ($links['links'] as $index => $link) {
        echo "Link #" . ($index + 1) . ":\n";
        echo "  Name: {$link['link_name']}\n";
        echo "  Type: {$link['link_type']}\n";
        echo "  Advertiser: {$link['advertiser_name']} (ID: {$link['advertiser_id']})\n";
        echo "  Commission: {$link['sale_commission']}\n";
        
        if ($link['seven_day_epc'] && $link['seven_day_epc'] !== 'N/A') {
            echo "  7-Day EPC: \${$link['seven_day_epc']}\n";
        }
        
        if ($link['three_month_epc'] && $link['three_month_epc'] !== 'N/A') {
            echo "  3-Month EPC: \${$link['three_month_epc']}\n";
        }
        
        echo "  Destination: {$link['destination']}\n";
        echo "  Tracking URL: {$link['click_url']}\n";
        
        if ($link['coupon_code']) {
            echo "  Coupon Code: {$link['coupon_code']}\n";
        }
        
        if ($link['description']) {
            $desc = substr($link['description'], 0, 100);
            echo "  Description: {$desc}...\n";
        }
        
        echo "\n";
    }
    
    // Example 2: Search by specific advertiser
    echo "\n2. Searching for links from specific advertiser:\n";
    echo str_repeat('-', 70) . "\n";
    
    $linksByAdvertiser = $client->products()->search([
        'website_id' => 'YOUR_WEBSITE_ID',
        'advertiser_ids' => '123456', // BraceAbility (replace with real advertiser ID)
        'records_per_page' => 10
    ]);
    
    echo "Total links from advertiser: {$linksByAdvertiser['total_matched']}\n\n";
    
    foreach (array_slice($linksByAdvertiser['links'], 0, 3) as $index => $link) {
        echo "Link #" . ($index + 1) . ": {$link['link_name']}\n";
        echo "  Commission: {$link['sale_commission']}\n";
        echo "  URL: {$link['click_url']}\n\n";
    }
    
    // Example 3: Filter by link type
    echo "\n3. Searching for Text Links only:\n";
    echo str_repeat('-', 70) . "\n";
    
    $textLinks = $client->products()->search([
        'website_id' => 'YOUR_WEBSITE_ID',
        'advertiser_ids' => '123456',
        'link_type' => 'Text Link',
        'records_per_page' => 5
    ]);
    
    echo "Text links found: {$textLinks['total_matched']}\n\n";
    
    // Example 4: Search for promotional links with coupons
    echo "\n4. Searching for promotional links:\n";
    echo str_repeat('-', 70) . "\n";
    
    $promoLinks = $client->products()->search([
        'website_id' => 'YOUR_WEBSITE_ID',
        'promotion_type' => 'Coupon',
        'records_per_page' => 5
    ]);
    
    echo "Promotional links found: {$promoLinks['total_matched']}\n\n";
    
    foreach ($promoLinks['links'] as $index => $link) {
        if ($link['coupon_code']) {
            echo "Link #" . ($index + 1) . ": {$link['link_name']}\n";
            echo "  Coupon: {$link['coupon_code']}\n";
            echo "  Valid: {$link['promotion_start_date']} to {$link['promotion_end_date']}\n\n";
        }
    }
    
    // Example 5: Pagination
    echo "\n5. Paginating through results:\n";
    echo str_repeat('-', 70) . "\n";
    
    for ($page = 1; $page <= 3; $page++) {
        $pagedResults = $client->products()->search([
            'website_id' => 'YOUR_WEBSITE_ID',
            'advertiser_ids' => '123456',
            'records_per_page' => 10,
            'page_number' => $page
        ]);
        
        echo "Page {$page}: {$pagedResults['records_returned']} links\n";
    }
    
    echo "\n=== Search completed successfully! ===\n";
    echo "\nNote: The 'products()' method uses the Link Search API, which returns\n";
    echo "affiliate links containing product information. CJ Affiliate does not\n";
    echo "offer a dedicated Product Search API.\n";
    
} catch (CJException $e) {
    echo "\nCJ Affiliate Error:\n";
    echo $e->getMessage() . "\n";
    echo "Code: " . $e->getCode() . "\n";
    
    if ($e->getResponse()) {
        echo "\nResponse details:\n";
        print_r($e->getResponse());
    }
} catch (\Exception $e) {
    echo "\nError: " . $e->getMessage() . "\n";
}
