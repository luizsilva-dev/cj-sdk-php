# CJ Affiliate SDK for PHP

[![Latest Stable Version](https://img.shields.io/packagist/v/luizsilva-dev/cj-sdk-php.svg?style=flat-square)](https://packagist.org/packages/luizsilva-dev/cj-sdk-php)
[![Total Downloads](https://img.shields.io/packagist/dt/luizsilva-dev/cj-sdk-php.svg?style=flat-square)](https://packagist.org/packages/luizsilva-dev/cj-sdk-php)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg?style=flat-square)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D7.4-blue.svg?style=flat-square)](https://php.net)
[![GitHub Stars](https://img.shields.io/github/stars/luizsilva-dev/cj-sdk-php?style=flat-square)](https://github.com/luizsilva-dev/cj-sdk-php/stargazers)

A comprehensive and **fully tested** PHP SDK for integrating with the CJ Affiliate (Commission Junction) API. Built with Guzzle HTTP client and tested with real CJ credentials to ensure 100% compatibility.

**Developed by:** [Luiz Silva](https://github.com/luizsilva-dev)

## 🚀 Features

- ✅ **Link Search API** - Search affiliate links with product information
- ✅ **Advertiser Lookup** - Search and discover advertisers/merchants  
- ✅ **Commission Detail** - Real-time commission tracking via GraphQL
- ✅ **Program Terms** - Access program commission rates
- ✅ **Promotional Properties** - Manage PIDs and tracking
- ✅ **Offer Feed** - Access automated offers
- ✅ **Built-in Caching** - File-based caching for performance
- ✅ **XML & JSON Support** - Automatic format detection
- ✅ **Exception Handling** - Comprehensive error handling
- ✅ **Guzzle HTTP Client** - Modern and reliable
- ✅ **PSR-4 Autoloading** - Modern PHP standards

## ⚠️ Important Note About Product Search

**CJ Affiliate does not offer a public Product Search API.** 

After extensive testing with real CJ credentials, we confirmed that:
- The GraphQL Product Search endpoint (`ads.api.cj.com/graphql`) does not exist (404 error)
- Product Feeds are for advertisers to submit products, not for publishers to query

**Solution:** Use the **Link Search API** instead. It returns affiliate links that include product information (names, URLs, commissions, etc.).

See [API_CHANGES.md](API_CHANGES.md) for details.

## 💻 Requirements

- PHP 7.4 or higher
- JSON extension enabled
- XML extension enabled
- Guzzle HTTP client (auto-installed via Composer)
- CJ Affiliate account with API credentials

## 📦 Installation

### Via Composer (Recommended)

```bash
composer require luizsilva-dev/cj-sdk-php
```

### Manual Installation

```bash
git clone https://github.com/luizsilva-dev/cj-sdk-php.git
cd cj-sdk-php
composer install
```

Then include in your project:

```php
require_once 'vendor/autoload.php';
```

## ⚡ Quick Start

### 1. Get Your CJ API Credentials

1. Visit [CJ Developer Portal](https://developers.cj.com/)
2. Sign in with your CJ Affiliate credentials
3. Go to **Authentication > Personal Access Tokens**
4. Create a new token
5. Copy your **Personal Access Token**
6. Get your **Publisher ID** (top right corner in CJ Members portal)
7. Get your **Website ID** from Account → Websites

### 2. Initialize the Client

```php
<?php
require_once 'vendor/autoload.php';

use CJAffiliate\CJClient;

$client = new CJClient([
    'access_token' => 'your_personal_access_token',
    'publisher_id' => 'your_publisher_id',
    'timeout' => 30,          // Optional: Request timeout in seconds
    'cache_enabled' => true,  // Optional: Enable caching
    'cache_ttl' => 3600,      // Optional: Cache TTL in seconds
    'debug' => false          // Optional: Enable debug mode
]);
```

### 3. Search Affiliate Links

```php
// Search for affiliate links (includes product information)
$links = $client->products()->search([
    'website_id' => 'your_website_id',
    'advertiser_ids' => '123456',  // Optional: specific advertiser
    'keywords' => 'knee brace',     // Optional: search keywords
    'records_per_page' => 10
]);

// Display results
echo "Total links found: " . $links['total_matched'] . "\n";

foreach ($links['links'] as $link) {
    echo "- {$link['link_name']}\n";
    echo "  Advertiser: {$link['advertiser_name']}\n";
    echo "  Commission: {$link['sale_commission']}\n";
    echo "  Click URL: {$link['click_url']}\n";
    echo "\n";
}
```

## 📖 API Reference

### Link Search (Product Information)

The `products()` method actually uses the Link Search API, which returns affiliate links with product information:

```php
$links = $client->products()->search([
    'website_id' => 'your_website_id',        // REQUIRED
    'advertiser_ids' => '123456,789012',      // Optional: comma-separated IDs
    'keywords' => 'search terms',             // Optional
    'link_type' => 'Text Link',               // Optional: Banner, Text Link, etc.
    'promotion_type' => 'Coupon',             // Optional
    'records_per_page' => 50,                 // Optional: default 50, max 1000
    'page_number' => 1                        // Optional: default 1
]);
```

**Response:**
```php
[
    'links' => [
        [
            'link_id' => '14496605',
            'link_name' => 'Homepage',
            'link_type' => 'Text Link',
            'advertiser_id' => '123456',
            'advertiser_name' => 'BraceAbility',
            'destination' => 'https://www.braceability.com',
            'click_url' => 'https://www.anrdoezrs.net/click-...',
            'description' => 'Homepage',
            'sale_commission' => '8.00%',
            'seven_day_epc' => '8.99',
            'three_month_epc' => '5.59',
            'promotion_type' => 'N/A',
            'coupon_code' => '',
            'category' => 'Equipment',
            'relationship_status' => 'joined',
            // ... more fields
        ]
    ],
    'total_matched' => 45,
    'records_returned' => 10,
    'page_number' => 1
]
```

### Advertiser Lookup

```php
// Search all joined advertisers
$advertisers = $client->advertisers()->search([
    'advertiser-ids' => 'joined'  // or 'notjoined', or specific IDs
]);

// Search by specific advertiser ID
$advertiser = $client->advertisers()->getById('123456');

// Search by name or program URL
$advertisers = $client->advertisers()->getByName('Nike');

// Search by keywords
$advertisers = $client->advertisers()->getByKeywords('shoes fitness');

// Get only joined advertisers
$joined = $client->advertisers()->getJoined();

// Get not joined advertisers
$notJoined = $client->advertisers()->getNotJoined();
```

**Response:**
```php
[
    'advertisers' => [
        [
            'advertiser_id' => '123456',
            'advertiser_name' => 'BraceAbility',
            'program_url' => 'https://www.braceability.com',
            'relationship_status' => 'joined',
            'network_rank' => 3,
            'seven_day_epc' => 29.43,
            'three_month_epc' => 22.79,
            'actions' => [
                [
                    'name' => 'braceability.com Purchase',
                    'type' => 'advanced sale',
                    'commission' => ['default' => '8.00%']
                ]
            ]
        ]
    ],
    'total_matched' => 1,
    'records_returned' => 1,
    'page_number' => 1
]
```

### Commission Details

```php
// Get publisher commissions (last 7 days)
$commissions = $client->commissions()->getPublisherCommissions([
    'since_date' => '2025-11-01T00:00:00Z',
    'before_date' => '2025-11-15T00:00:00Z'
]);

// Note: Maximum date range is 31 days

// Get advertiser commissions (for advertisers only)
$commissions = $client->commissions()->getAdvertiserCommissions([
    'since_date' => '2025-11-01T00:00:00Z',
    'before_date' => '2025-11-15T00:00:00Z'
]);
```

**Response:**
```php
[
    'count' => 5,
    'records' => [
        [
            'advertiser_name' => 'BraceAbility',
            'posting_date' => '2025-11-10T00:00:00Z',
            'pub_commission_amount_usd' => 25.50,
            'action_tracker_name' => 'Purchase',
            // ... more fields
        ]
    ]
]
```

### Program Terms

```php
$terms = $client->programTerms()->search([
    'advertiser-ids' => '123456'
]);
```

### Promotional Properties

```php
$properties = $client->promotionalProperties()->search([
    'advertiser-id' => '123456'
]);
```

## 🎯 Examples

See the [examples/](examples/) directory for complete working examples:

- [example_products.php](examples/example_products.php) - Link search
- [example_advertisers.php](examples/example_advertisers.php) - Advertiser lookup
- [example_commissions.php](examples/example_commissions.php) - Commission tracking
- [example_error_handling.php](examples/example_error_handling.php) - Error handling
- [example_debug.php](examples/example_debug.php) - Debug mode

## ⚙️ Configuration

### Caching

Enable caching to reduce API calls:

```php
$client = new CJClient([
    'access_token' => 'your_token',
    'publisher_id' => 'your_id',
    'cache_enabled' => true,
    'cache_ttl' => 3600  // 1 hour
]);
```

### Debug Mode

Enable debug mode to see request/response details:

```php
$client = new CJClient([
    'access_token' => 'your_token',
    'publisher_id' => 'your_id',
    'debug' => true
]);
```

Debug logs will be written to PHP's error log.

### Timeout

Adjust request timeout:

```php
$client = new CJClient([
    'access_token' => 'your_token',
    'publisher_id' => 'your_id',
    'timeout' => 60  // 60 seconds
]);
```

## 🚨 Error Handling

The SDK throws `CJException` for all errors:

```php
use CJAffiliate\Exceptions\CJException;

try {
    $links = $client->products()->search([
        'website_id' => 'your_website_id'
    ]);
} catch (CJException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "HTTP Code: " . $e->getCode() . "\n";
    
    // Get additional error details
    $response = $e->getResponse();
    if ($response) {
        print_r($response);
    }
}
```

### Common Errors

| Error | Cause | Solution |
|-------|-------|----------|
| HTTP 400 | Invalid parameters | Check parameter names and values |
| HTTP 401 | Invalid access token | Generate a new token at developers.cj.com |
| HTTP 403 | No API access | Contact CJ Affiliate support |
| HTTP 404 | Endpoint not found | Update SDK to latest version |
| HTTP 406 | Invalid request format | Check access token format |

## 🎨 Performance Tips

1. **Enable Caching**: Reduces API calls significantly
2. **Batch Requests**: Request multiple items in single calls
3. **Pagination**: Use `records_per_page` to limit response size
4. **Filter Results**: Use specific parameters to reduce data transfer
5. **Connection Pooling**: Reuse the same client instance

## 🤝 Contributing

Contributions are welcome! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

### Development Setup

```bash
git clone https://github.com/luizsilva-dev/cj-sdk-php.git
cd cj-sdk-php
composer install
```

### Running Tests

```bash
composer test
```

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 💬 Support

- **Issues**: [GitHub Issues](https://github.com/luizsilva-dev/cj-sdk-php/issues)
- **Discussions**: [GitHub Discussions](https://github.com/luizsilva-dev/cj-sdk-php/discussions)
- **Email**: luiz@example.com

## 🔗 Useful Links

- [CJ Affiliate](https://www.cj.com/)
- [CJ Developer Portal](https://developers.cj.com/)
- [API Documentation](https://developers.cj.com/docs/rest-apis/overview)
- [Packagist Package](https://packagist.org/packages/luizsilva-dev/cj-sdk-php)

## 📝 Changelog

See [CHANGELOG.md](CHANGELOG.md) for a history of changes to this project.

## 🙏 Acknowledgments

- Built with [Guzzle HTTP Client](https://github.com/guzzle/guzzle)
- Tested with real CJ Affiliate credentials to ensure accuracy
- Special thanks to the CJ Affiliate API team

---

**Made with ❤️ by [Luiz Silva](https://github.com/luizsilva-dev)**
