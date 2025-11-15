<?php

namespace CJAffiliate\API;

use CJAffiliate\CJClient;
use CJAffiliate\Utils\HttpClient;
use CJAffiliate\Utils\Cache;
use CJAffiliate\Exceptions\CJException;

/**
 * CJ Product Search API (GraphQL)
 * 
 * Search advertiser product catalogs using the CJ Affiliate Product Feed API.
 * 
 * @package CJAffiliate\API
 */
class ProductSearch
{
    /**
     * CJ Product Feed GraphQL API Endpoint
     */
    private const API_ENDPOINT = 'https://ads.api.cj.com/query';
    
    /**
     * CJ Client
     * @var CJClient
     */
    private $client;
    
    /**
     * HTTP Client
     * @var HttpClient
     */
    private $http;
    
    /**
     * Cache
     * @var Cache
     */
    private $cache;
    
    /**
     * Constructor
     * 
     * @param CJClient $client
     */
    public function __construct(CJClient $client)
    {
        $this->client = $client;
        $this->http = new HttpClient(
            $client->getAccessToken(),
            $client->getConfig('timeout', 30),
            $client->getConfig('debug', false)
        );
        $this->cache = new Cache(
            $client->getConfig('cache_enabled', false),
            $client->getConfig('cache_ttl', 3600)
        );
    }
    
    /**
     * Search products from advertiser catalogs
     * 
     * @param array $params Search parameters
     * @return array
     * @throws CJException
     * 
     * Accepted parameters:
     * - advertiser_ids (array|string): Advertiser IDs to search (optional)
     * - keywords (array|string): Search keywords (optional)
     * - limit (int): Maximum results (default: 50, max: 10000)
     * - records_per_page (int): Alias for limit (backwards compatibility)
     * - offset (int): Starting record (default: 0)
     * 
     * Note: The website_id from CJClient config is automatically used as PID for affiliate links
     */
    public function search(array $params = []): array
    {
        // Cache key
        $cacheKey = 'product_search_' . md5(json_encode($params));
        
        // Check cache
        $cached = $this->cache->get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }
        
        // Build GraphQL query
        $query = $this->buildGraphQLQuery($params);
        
        try {
            // Make POST request to GraphQL API
            $response = $this->http->post(self::API_ENDPOINT, ['query' => $query], true);
            
            // Process response
            $result = $this->processResponse($response);
            
            // Save to cache
            $this->cache->set($cacheKey, $result);
            
            return $result;
            
        } catch (CJException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new CJException(
                "Failed to search products: " . $e->getMessage(),
                $e->getCode()
            );
        }
    }
    
    /**
     * Build GraphQL query for product search
     * 
     * @param array $params
     * @return string
     */
    private function buildGraphQLQuery(array $params): string
    {
        // Get publisher ID (companyId)
        $companyId = $this->client->getConfig('publisher_id');
        if (empty($companyId)) {
            throw new CJException('publisher_id is required in configuration');
        }
        
        // Build query arguments
        $args = ['companyId: "' . $companyId . '"'];
        
        // Add advertiser IDs filter
        if (!empty($params['advertiser_ids'])) {
            $advertiserIds = is_array($params['advertiser_ids']) 
                ? $params['advertiser_ids'] 
                : explode(',', $params['advertiser_ids']);
            $idsStr = implode('", "', array_map('trim', $advertiserIds));
            $args[] = 'partnerIds: ["' . $idsStr . '"]';
        }
        
        // Add keywords filter
        if (!empty($params['keywords'])) {
            $keywords = is_array($params['keywords']) 
                ? $params['keywords'] 
                : [$params['keywords']];
            $keywordsStr = implode('", "', array_map('addslashes', $keywords));
            $args[] = 'keywords: ["' . $keywordsStr . '"]';
        }
        
        // Add limit (support both 'limit' and 'records_per_page' for backwards compatibility)
        $limit = isset($params['limit']) ? (int)$params['limit'] : (isset($params['records_per_page']) ? (int)$params['records_per_page'] : 50);
        $args[] = 'limit: ' . min($limit, 10000);
        
        // Add offset
        if (isset($params['offset']) && $params['offset'] > 0) {
            $args[] = 'offset: ' . (int)$params['offset'];
        }
        
        $argsStr = implode(', ', $args);
        
        // Get PID for affiliate tracking links (uses website_id from config)
        $pid = $this->client->getWebsiteId();
        
        // Build linkCode field with PID
        $linkCodeField = 'linkCode(pid: "' . $pid . '") { clickUrl }';
        
        // Build complete GraphQL query
        $query = <<<GRAPHQL
{
  products($argsStr) {
    totalCount
    count
    resultList {
      id
      title
      description
      price {
        amount
        currency
      }
      salePrice {
        amount
        currency
      }
      link
      imageLink
      brand
      advertiserId
      advertiserName
      $linkCodeField
    }
  }
}
GRAPHQL;
        
        return $query;
    }
    
    /**
     * Get specific product by ID
     * 
     * @param string $productId
     * @param string $advertiserId (optional)
     * @return array|null
     * @throws CJException
     */
    public function getById(string $productId, string $advertiserId = null): ?array
    {
        $params = ['limit' => 1];
        
        if ($advertiserId) {
            $params['advertiser_ids'] = $advertiserId;
        }
        
        $results = $this->search($params);
        
        foreach ($results['products'] as $product) {
            if ($product['id'] === $productId) {
                return $product;
            }
        }
        
        return null;
    }
    
    /**
     * Process API response from Product Feed GraphQL API
     * 
     * @param array $response
     * @return array
     */
    private function processResponse(array $response): array
    {
        $products = [];
        
        // Extract from GraphQL response
        $data = $response['data']['products'] ?? [];
        $totalCount = $data['totalCount'] ?? 0;
        $count = $data['count'] ?? 0;
        $productList = $data['resultList'] ?? [];
        
        foreach ($productList as $product) {
            if (empty($product) || !is_array($product)) {
                continue;
            }
            
            $products[] = [
                'id' => $product['id'] ?? null,
                'title' => $product['title'] ?? '',
                'description' => $product['description'] ?? '',
                'price' => [
                    'amount' => $product['price']['amount'] ?? '0.00',
                    'currency' => $product['price']['currency'] ?? 'USD'
                ],
                'sale_price' => isset($product['salePrice']) ? [
                    'amount' => $product['salePrice']['amount'] ?? null,
                    'currency' => $product['salePrice']['currency'] ?? 'USD'
                ] : null,
                'link' => $product['link'] ?? '',
                'affiliate_link' => isset($product['linkCode']['clickUrl']) ? $product['linkCode']['clickUrl'] : null,
                'image_url' => $product['imageLink'] ?? '',
                'brand' => $product['brand'] ?? '',
                'advertiser_id' => $product['advertiserId'] ?? null,
                'advertiser_name' => $product['advertiserName'] ?? '',
                'raw' => $product
            ];
        }
        
        return [
            'products' => $products,
            'total_count' => (int) $totalCount,
            'count' => (int) $count
        ];
    }
}
