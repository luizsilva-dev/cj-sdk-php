<?php
/**
 * Example: CJ Affiliate Program Terms API
 * 
 * This example demonstrates how to retrieve program commission rates
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
    
    echo "=== CJ Affiliate Program Terms API ===\n\n";
    
    // Example 1: List all programs
    echo "1. Listing all programs:\n";
    echo str_repeat('-', 50) . "\n";
    
    $programs = $client->programTerms()->listPrograms();
    
    if (isset($programs['data']['publisherPrograms']['programs'])) {
        $programList = $programs['data']['publisherPrograms']['programs'];
        echo "Total programs: " . count($programList) . "\n\n";
        
        foreach (array_slice($programList, 0, 10) as $program) {
            echo "Advertiser: {$program['advertiserName']}\n";
            echo "  ID: {$program['advertiserId']}\n";
            echo "  Relationship: {$program['relationshipStatus']}\n";
            echo "  Status: {$program['programStatus']}\n\n";
        }
    }
    
    // Example 2: Get specific program terms
    echo "\n2. Getting program terms for specific advertiser:\n";
    echo str_repeat('-', 50) . "\n";
    
    $advertiserId = '123456'; // Replace with actual ID
    
    $terms = $client->programTerms()->getProgramTerms($advertiserId);
    
    if (isset($terms['data']['programTerms'])) {
        $programData = $terms['data']['programTerms'];
        echo "Advertiser: {$programData['advertiserName']}\n\n";
        
        if (isset($programData['situations'])) {
            echo "Commission Situations:\n";
            foreach ($programData['situations'] as $situation) {
                echo "  - {$situation['situationName']}\n";
                echo "    Rate: {$situation['commissionRate']}\n";
                echo "    Type: {$situation['commissionType']}\n\n";
            }
        }
    }
    
    echo "\n=== Done! ===\n";
    
} catch (CJException $e) {
    echo "\nCJ Affiliate Error:\n";
    echo $e->getDetails();
} catch (\Exception $e) {
    echo "\nError: " . $e->getMessage() . "\n";
}
