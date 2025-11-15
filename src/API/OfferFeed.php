<?php

namespace CJAffiliate\API;

use CJAffiliate\CJClient;
use CJAffiliate\Utils\HttpClient;
use CJAffiliate\Utils\Cache;
use CJAffiliate\Exceptions\CJException;

/**
 * API de Feed de Ofertas CJ (REST)
 * 
 * Acessa ofertas e promoções automatizadas
 * 
 * @package CJAffiliate\API
 */
class OfferFeed
{
    private const API_ENDPOINT = 'https://api.cj.com/query';
    
    private $client;
    private $http;
    private $cache;
    
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
     * Buscar ofertas
     * 
     * @param array $params
     * @return array
     * @throws CJException
     */
    public function search(array $params = []): array
    {
        $cacheKey = 'offers_' . md5(json_encode($params));
        $cached = $this->cache->get($cacheKey);
        
        if ($cached !== null) {
            return $cached;
        }
        
        $defaults = [
            'records-per-page' => 50,
            'page-number' => 1
        ];
        
        $params = array_merge($defaults, $params);
        
        try {
            $response = $this->http->get(self::API_ENDPOINT, $params);
            $this->cache->set($cacheKey, $response);
            return $response;
            
        } catch (CJException $e) {
            throw $e;
        }
    }
    
    /**
     * Buscar ofertas ativas
     * 
     * @param array $additionalParams
     * @return array
     * @throws CJException
     */
    public function getActiveOffers(array $additionalParams = []): array
    {
        $params = array_merge(
            ['status' => 'active'],
            $additionalParams
        );
        
        return $this->search($params);
    }
}
