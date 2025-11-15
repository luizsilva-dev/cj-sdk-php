<?php
/**
 * Example: CJ Affiliate Commission Detail API
 * 
 * This example demonstrates how to retrieve commission data
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

// Configuration
$config = [
    'access_token' => 'YOUR_PERSONAL_ACCESS_TOKEN',
    'publisher_id' => 'YOUR_PUBLISHER_ID',
    'cache_enabled' => true
];

try {
    $client = new CJClient($config);
    
    echo "=== CJ Affiliate Commission Detail API ===\n\n";
    
    // Example 1: Get recent commissions
    echo "1. Fetching recent commissions (last 30 days):\n";
    echo str_repeat('-', 50) . "\n";
    
    $commissions = $client->commissions()->getPublisherCommissions([
        'start_date' => date('Y-m-d', strtotime('-30 days')),
        'end_date' => date('Y-m-d')
    ]);
    
    if (isset($commissions['data']['publisherCommissions']['records'])) {
        $records = $commissions['data']['publisherCommissions']['records'];
        echo "Total commissions found: " . count($records) . "\n\n";
        
        foreach (array_slice($records, 0, 5) as $index => $record) {
            echo "Commission #" . ($index + 1) . ":\n";
            echo "  Order ID: {$record['orderId']}\n";
            echo "  Advertiser: {$record['advertiserName']}\n";
            echo "  Commission: \${$record['pubCommissionAmountUsd']}\n";
            echo "  Sale Amount: \${$record['saleAmount']}\n";
            echo "  Status: {$record['actionStatus']}\n";
            echo "  Action Date: {$record['actionDate']}\n\n";
        }
    }
    
    // Example 2: Get commission summary
    echo "\n2. Commission Summary (Current Month):\n";
    echo str_repeat('-', 50) . "\n";
    
    $summary = $client->commissions()->getSummary([
        'start_date' => date('Y-m-01'),
        'end_date' => date('Y-m-d')
    ]);
    
    echo "Period: {$summary['period']['start']} to {$summary['period']['end']}\n";
    echo "Total Commissions: {$summary['total_commissions']}\n";
    echo "Total Amount: \${$summary['total_amount']}\n\n";
    
    echo "By Status:\n";
    foreach ($summary['by_status'] as $status => $count) {
        echo "  {$status}: {$count}\n";
    }
    
    echo "\nTop Advertisers:\n";
    arsort($summary['by_advertiser']);
    $topAdvertisers = array_slice($summary['by_advertiser'], 0, 5, true);
    
    foreach ($topAdvertisers as $advertiser => $data) {
        echo "  {$advertiser}: {$data['count']} commissions (\${$data['amount']})\n";
    }
    
    echo "\n=== Done! ===\n";
    
} catch (CJException $e) {
    echo "\nCJ Affiliate Error:\n";
    echo $e->getDetails();
} catch (\Exception $e) {
    echo "\nError: " . $e->getMessage() . "\n";
}
