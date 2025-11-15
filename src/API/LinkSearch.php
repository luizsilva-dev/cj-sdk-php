<?php

namespace CJAffiliate\API;

use CJAffiliate\CJClient;
use CJAffiliate\Utils\HttpClient;
use CJAffiliate\Utils\Cache;
use CJAffiliate\Exceptions\CJException;

/**
 * API de busca de links de afiliados CJ (REST)
 * 
 * @package CJAffiliate\API
 */
class LinkSearch
{
    /**
     * Endpoint da API Link Search
     */
    private const API_ENDPOINT = 'https://linksearch.api.cj.com/v2/link-search';
    
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
     * Busca links de afiliados
     * 
     * @param array $params Parâmetros de busca
     * @return array
     * @throws CJException
     * 
     * Parâmetros aceitos:
     * - website-id (string): ID do website (obrigatório)
     * - advertiser-ids (string): IDs dos anunciantes separados por vírgula
     * - keywords (string): Palavras-chave
     * - link-type (string): Tipo do link (Banner, Text Link, Advanced, etc.)
     * - promotion-type (string): Tipo de promoção (coupon, sale, etc.)
     * - category (string): Categoria
     * - relationship-status (string): joined ou not-joined
     * - records-per-page (int): Registros por página (padrão: 50)
     * - page-number (int): Número da página (padrão: 1)
     */
    public function search(array $params = []): array
    {
        // Validate required website-id
        if (empty($params['website-id'])) {
            throw new CJException('Parameter website-id is required for link search');
        }
        
        // Cache key
        $cacheKey = 'link_search_' . md5(json_encode($params));
        
        // Verifica cache
        $cached = $this->cache->get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }
        
        // Parâmetros padrão
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
                "Erro ao buscar links: " . $e->getMessage(),
                $e->getCode()
            );
        }
    }
    
    /**
     * Busca links por anunciante específico
     * 
     * @param string $websiteId
     * @param string $advertiserId
     * @param array $additionalParams
     * @return array
     * @throws CJException
     */
    public function getByAdvertiser(string $websiteId, string $advertiserId, array $additionalParams = []): array
    {
        $params = array_merge(
            ['website-id' => $websiteId, 'advertiser-ids' => $advertiserId],
            $additionalParams
        );
        
        return $this->search($params);
    }
    
    /**
     * Busca links por palavras-chave
     * 
     * @param string $websiteId
     * @param string $keywords
     * @param array $additionalParams
     * @return array
     * @throws CJException
     */
    public function getByKeywords(string $websiteId, string $keywords, array $additionalParams = []): array
    {
        $params = array_merge(
            ['website-id' => $websiteId, 'keywords' => $keywords],
            $additionalParams
        );
        
        return $this->search($params);
    }
    
    /**
     * Processa resposta da API
     * 
     * @param array $response
     * @return array
     */
    private function processResponse(array $response): array
    {
        $links = [];
        
        // Extrai links da resposta
        if (isset($response['links']) && is_array($response['links'])) {
            foreach ($response['links'] as $link) {
                $links[] = [
                    'link_id' => $link['linkId'] ?? $link['link-id'] ?? null,
                    'link_name' => $link['linkName'] ?? $link['link-name'] ?? '',
                    'link_type' => $link['linkType'] ?? $link['link-type'] ?? '',
                    'advertiser_id' => $link['advertiserId'] ?? $link['advertiser-id'] ?? null,
                    'advertiser_name' => $link['advertiserName'] ?? $link['advertiser-name'] ?? '',
                    'category' => $link['category'] ?? '',
                    'link_code_html' => $link['linkCodeHtml'] ?? $link['link-code-html'] ?? '',
                    'link_code_javascript' => $link['linkCodeJavascript'] ?? $link['link-code-javascript'] ?? '',
                    'description' => $link['description'] ?? '',
                    'destination' => $link['destination'] ?? '',
                    'click_commission' => $link['clickCommission'] ?? $link['click-commission'] ?? '',
                    'sale_commission' => $link['saleCommission'] ?? $link['sale-commission'] ?? '',
                    'relationship_status' => $link['relationshipStatus'] ?? $link['relationship-status'] ?? '',
                    'promotion_type' => $link['promotionType'] ?? $link['promotion-type'] ?? '',
                    'promotion_start_date' => $link['promotionStartDate'] ?? $link['promotion-start-date'] ?? null,
                    'promotion_end_date' => $link['promotionEndDate'] ?? $link['promotion-end-date'] ?? null,
                    'coupon_code' => $link['couponCode'] ?? $link['coupon-code'] ?? '',
                    'raw' => $link // Mantém dados originais
                ];
            }
        }
        
        return [
            'links' => $links,
            'total_matched' => $response['totalMatched'] ?? $response['total-matched'] ?? count($links),
            'records_returned' => $response['recordsReturned'] ?? $response['records-returned'] ?? count($links),
            'page_number' => $response['pageNumber'] ?? $response['page-number'] ?? 1
        ];
    }
}
