# Quick Start Guide - CJ Affiliate SDK

## Quick Installation

### Option 1: Composer (Recommended)

```bash
composer require luizsilva-dev/cj-sdk-php
```

### Option 2: Manual Installation

```bash
git clone https://github.com/luizsilva-dev/cj-sdk-php.git
# or download the ZIP and extract
```

## Getting Your CJ.com Credentials

### Step 1: Create Personal Access Token

1. Go to https://developers.cj.com/
2. Log in with your CJ Affiliate credentials
3. In the left menu, click on **Authentication**
4. Click on **Personal Access Tokens**
5. Click the **Create New Token** button
6. Give your token a name (e.g., "My Platform")
7. Click **Register**
8. **IMPORTANT**: Copy the token immediately and save it securely
   - The token will only be displayed once!
   - If you lose it, you'll need to create a new one

### Step 2: Get Your Publisher ID

1. Log in to the CJ Affiliate Members portal
2. Your Publisher ID appears in the top right corner
3. Write down this number

### Step 3: Get Your Website ID

1. In the CJ Members portal, go to **Account → Websites**
2. You'll see a list of your registered websites
3. Note the **Website ID** of the site you want to use
4. If you don't have one, register a new website

## Basic Usage

### 1. Create Configuration File

Create a `config.php` file in your project root:

```php
<?php

return [
    'access_token' => 'YOUR_PERSONAL_ACCESS_TOKEN',
    'publisher_id' => 'YOUR_PUBLISHER_ID',
    'website_id' => 'YOUR_WEBSITE_ID',
    'cache_enabled' => true,
    'cache_ttl' => 3600,
    'debug' => false
];
```

**IMPORTANT**: Add `config.php` to `.gitignore` to avoid exposing your credentials!

### 2. First Script - Search Products

Create a `test.php` file:

```php
<?php

require_once 'vendor/autoload.php'; // If installed via Composer

use CJAffiliate\CJClient;
use CJAffiliate\Exceptions\CJException;

// Load configuration
$config = require 'config.php';

try {
    // Initialize client
    $client = new CJClient($config);
    
    // Search products
    $products = $client->products()->search([
        'website_id' => $config['website_id'],
        'keywords' => 'laptop',
        'records_per_page' => 5
    ]);
    
    // Display results
    echo "Products found: {$products['total_matched']}\n\n";
    
    foreach ($products['products'] as $product) {
        echo "Name: {$product['name']}\n";
        echo "Price: {$product['currency']} {$product['price']}\n";
        echo "Merchant: {$product['advertiser_name']}\n";
        echo "Link: {$product['buy_url']}\n\n";
    }
    
} catch (CJException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
```

### 3. Run

```bash
php test.php
```

## Practical Examples

### Search Affiliate Links with Coupons

```php
$links = $client->links()->search([
    'website-id' => $config['website_id'],
    'promotion-type' => 'coupon',
    'relationship-status' => 'joined',
    'records-per-page' => 10
]);

foreach ($links['links'] as $link) {
    if ($link['coupon_code']) {
        echo "Coupon: {$link['coupon_code']}\n";
        echo "Merchant: {$link['advertiser_name']}\n";
        echo "Discount: {$link['link_name']}\n\n";
    }
}
```

### List Your Active Merchants

```php
$advertisers = $client->advertisers()->getJoined([
    'records-per-page' => 20
]);

foreach ($advertisers['advertisers'] as $advertiser) {
    echo "Merchant: {$advertiser['advertiser_name']}\n";
    echo "Category: {$advertiser['primary_category']}\n";
    echo "EPC: \${$advertiser['three_month_epc']}\n\n";
}
```

### Search Products with Price Filter

```php
$products = $client->products()->search([
    'website_id' => $config['website_id'],
    'keywords' => 'smartphone',
    'low_price' => 200,
    'high_price' => 500,
    'currency' => 'USD'
]);
```

## Integration with Your Affiliate Platform

### For a platform like dealtrust.us

```php
// 1. Search products by category
function getProductsByCategory($client, $websiteId, $category) {
    return $client->products()->search([
        'website_id' => $websiteId,
        'keywords' => $category,
        'records_per_page' => 50
    ]);
}

// 2. Display product on your site
function displayProduct($product) {
    echo '<div class="product">';
    echo '<img src="' . $product['image_url'] . '">';
    echo '<h3>' . $product['name'] . '</h3>';
    echo '<p class="price">$' . $product['price'] . '</p>';
    echo '<p class="merchant">' . $product['advertiser_name'] . '</p>';
    // Link already contains your affiliate tracking
    echo '<a href="' . $product['buy_url'] . '" target="_blank">View Offer</a>';
    echo '</div>';
}

// 3. When user clicks, they'll be redirected with your affiliate link
```

## Important Tips

### 1. Cache
Always enable cache in production to avoid excessive requests:

```php
$config = [
    'access_token' => '...',
    'publisher_id' => '...',
    'cache_enabled' => true,
    'cache_ttl' => 3600 // 1 hour
];
```

### 2. Error Handling
Always use try-catch:

```php
try {
    $products = $client->products()->search([...]);
} catch (CJException $e) {
    // Log error
    error_log($e->getMessage());
    
    // Display friendly message to user
    echo "Sorry, an error occurred. Please try again.";
}
```

### 3. Relationship Status
You can only promote products/links from advertisers with `relationship_status = 'joined'`.

To see only your active advertisers:

```php
$params = [
    'relationship-status' => 'joined'
];
```

### 4. Affiliate Links
The `buy_url` field in products already contains your affiliate tracking.
When the user clicks and makes a purchase, you'll receive the commission automatically.

### 5. Request Limits
- Respect CJ API rate limits
- Use cache to reduce requests
- Don't make more than 1 request per second

## Troubleshooting

### Error: "Access token is required"
- Verify you created the Personal Access Token correctly
- Confirm you copied the complete token

### Error: "Parameter website_id is required"
- You need to register a website in the CJ portal
- Use the correct Website ID in product and link searches

### Error: HTTP 401
- Your token may be invalid or expired
- Create a new token in the CJ portal

### Error: HTTP 403
- You may not have permission to access certain resources
- Verify your Publisher ID is correct

### No products returned
- Check if you have advertisers with `relationship_status = 'joined'`
- Try searching without filters first
- Use more generic keywords

## Next Steps

1. **Explore examples**: See the `examples/` folder for complete use cases
2. **Implement database cache**: For better performance
3. **Add tracking**: Monitor clicks and conversions
4. **Create categories**: Organize products by categories on your site
5. **Implement search**: Add product search in the frontend

## Additional Resources

- Official CJ documentation: https://developers.cj.com/
- Complete README: `README.md`
- Integration examples: `examples/example_integration.php`

## Support

For questions about the CJ API:
- Visit: https://developers.cj.com/
- CJ Support: Contact through the Members portal

For SDK questions:
- Open an issue on the repository
- See complete documentation in README.md
