<?php

namespace CJAffiliate\API;

use CJAffiliate\CJClient;
use CJAffiliate\Utils\HttpClient;
use CJAffiliate\Utils\Cache;
use CJAffiliate\Exceptions\CJException;

/**
 * API de busca de anunciantes CJ (REST)
 * 
 * @package CJAffiliate\API
 */
class AdvertiserLookup
{
    /**
     * Endpoint da API Advertiser Lookup
     */
    private const API_ENDPOINT = 'https://advertiser-lookup.api.cj.com/v3/advertiser-lookup';
    
    /**
     * Cliente CJ
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
     * Construtor
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
     * Search advertisers
     * 
     * @param array $params Search parameters
     * @return array
     * @throws CJException
     * 
     * Accepted parameters:
     * - requestor-cid (string): Your Publisher CID (REQUIRED - uses publisher_id from config if not provided)
     * - advertiser-ids (string): 'joined', 'notjoined', or comma-separated CIDs
     * - advertiser-name (string): Advertiser name or program URL
     * - keywords (string): Search keywords
     * - page-number (int): Page number (default: 1)
     * - records-per-page (int): Records per page (default: 50, max: 100)
     * - mobile-tracking-certified (boolean): Filter by mobile certified (true/false)
     */
    public function search(array $params = []): array
    {
        // Add requestor-cid automatically if not provided
        if (!isset($params['requestor-cid'])) {
            $params['requestor-cid'] = $this->client->getPublisherId();
        }
        
        // Cache key
        $cacheKey = 'advertiser_lookup_' . md5(json_encode($params));
        
        // Check cache
        $cached = $this->cache->get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }
        
        // Default parameters
        if (!isset($params['records-per-page'])) {
            $params['records-per-page'] = 50;
        }
        
        if (!isset($params['page-number'])) {
            $params['page-number'] = 1;
        }
        
        try {
            // Faz requisição GET
            $response = $this->http->get(self::API_ENDPOINT, $params);
            
            // Processa resposta
            $result = $this->processResponse($response);
            
            // Salva no cache
            $this->cache->set($cacheKey, $result);
            
            return $result;
            
        } catch (CJException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new CJException(
                "Failed to search advertisers: " . $e->getMessage(),
                $e->getCode()
            );
        }
    }
    
    /**
     * Busca anunciante por ID
     * 
     * @param string $advertiserId
     * @return array|null
     * @throws CJException
     */
    public function getById(string $advertiserId): ?array
    {
        $results = $this->search(['advertiser-ids' => $advertiserId]);
        return $results['advertisers'][0] ?? null;
    }
    
    /**
     * Get joined advertisers (advertisers you have a relationship with)
     * 
     * @param array $additionalParams Additional parameters
     * @return array
     * @throws CJException
     */
    public function getJoined(array $additionalParams = []): array
    {
        $params = array_merge(
            ['advertiser-ids' => 'joined'],
            $additionalParams
        );
        
        return $this->search($params);
    }
    
    /**
     * Get not joined advertisers (advertisers you don't have a relationship with)
     * 
     * @param array $additionalParams Additional parameters
     * @return array
     * @throws CJException
     */
    public function getNotJoined(array $additionalParams = []): array
    {
        $params = array_merge(
            ['advertiser-ids' => 'notjoined'],
            $additionalParams
        );
        
        return $this->search($params);
    }
    
    /**
     * Search advertisers by name or program URL
     * 
     * @param string $name Advertiser name or program URL
     * @param array $additionalParams Additional parameters
     * @return array
     * @throws CJException
     */
    public function getByName(string $name, array $additionalParams = []): array
    {
        $params = array_merge(
            ['advertiser-name' => $name],
            $additionalParams
        );
        
        return $this->search($params);
    }
    
    /**
     * Search advertisers by keywords
     * 
     * @param string $keywords Search keywords
     * @param array $additionalParams Additional parameters
     * @return array
     * @throws CJException
     */
    public function getByKeywords(string $keywords, array $additionalParams = []): array
    {
        $params = array_merge(
            ['keywords' => $keywords],
            $additionalParams
        );
        
        return $this->search($params);
    }
    
    /**
     * Process API response
     * 
     * @param array $response
     * @return array
     */
    private function processResponse(array $response): array
    {
        $advertisers = [];
        
        // Extract metadata from attributes
        $totalMatched = $response['@total-matched'] ?? $response['total-matched'] ?? 0;
        $recordsReturned = $response['@records-returned'] ?? $response['records-returned'] ?? 0;
        $pageNumber = $response['@page-number'] ?? $response['page-number'] ?? 1;
        
        // Extract advertisers
        $advertiserData = $response['advertiser'] ?? [];
        
        // Handle single advertiser (not in array)
        if (!empty($advertiserData) && !isset($advertiserData[0])) {
            $advertiserData = [$advertiserData];
        }
        
        foreach ($advertiserData as $advertiser) {
            // Skip if empty
            if (empty($advertiser) || !is_array($advertiser)) {
                continue;
            }
            
            $advertisers[] = [
                'advertiser_id' => $advertiser['advertiser-id'] ?? $advertiser['@advertiser-id'] ?? null,
                'advertiser_name' => $advertiser['advertiser-name'] ?? $advertiser['@advertiser-name'] ?? '',
                'program_url' => $advertiser['program-url'] ?? $advertiser['@program-url'] ?? '',
                'relationship_status' => $advertiser['relationship-status'] ?? $advertiser['@relationship-status'] ?? '',
                'network_rank' => $advertiser['network-rank'] ?? $advertiser['@network-rank'] ?? 0,
                'primary_category' => $advertiser['primary-category'] ?? $advertiser['@primary-category'] ?? '',
                'performance_incentives' => $advertiser['performance-incentives'] ?? $advertiser['@performance-incentives'] ?? false,
                'actions' => $this->extractActions($advertiser),
                'seven_day_epc' => $advertiser['seven-day-epc'] ?? $advertiser['@seven-day-epc'] ?? 0,
                'three_month_epc' => $advertiser['three-month-epc'] ?? $advertiser['@three-month-epc'] ?? 0,
                'language' => $advertiser['language'] ?? $advertiser['@language'] ?? 'en',
                'raw' => $advertiser
            ];
        }
        
        return [
            'advertisers' => $advertisers,
            'total_matched' => (int) $totalMatched,
            'records_returned' => (int) $recordsReturned,
            'page_number' => (int) $pageNumber
        ];
    }
    
    /**
     * Extract actions from advertiser data
     * 
     * @param array $advertiser
     * @return array
     */
    private function extractActions(array $advertiser): array
    {
        $actions = [];
        
        if (isset($advertiser['actions']['action'])) {
            $actionData = $advertiser['actions']['action'];
            
            // Handle single action
            if (!isset($actionData[0])) {
                $actionData = [$actionData];
            }
            
            foreach ($actionData as $action) {
                if (is_array($action)) {
                    $actions[] = $action;
                }
            }
        }
        
        return $actions;
    }
}
