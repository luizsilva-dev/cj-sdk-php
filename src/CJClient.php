<?php

namespace CJAffiliate;

use CJAffiliate\API\ProductSearch;
use CJAffiliate\API\LinkSearch;
use CJAffiliate\API\AdvertiserLookup;
use CJAffiliate\API\CommissionDetail;
use CJAffiliate\API\ProgramTerms;
use CJAffiliate\API\PromotionalProperties;
use CJAffiliate\API\OfferFeed;
use CJAffiliate\Exceptions\CJException;

/**
 * Cliente principal para integração com CJ Affiliate (Commission Junction)
 * 
 * @package CJAffiliate
 */
class CJClient
{
    /**
     * Access token para autenticação
     * @var string
     */
    private $accessToken;
    
    /**
     * Publisher ID
     * @var string
     */
    private $publisherId;
    
    /**
     * Configurações do cliente
     * @var array
     */
    private $config;
    
    /**
     * Instância de ProductSearch
     * @var ProductSearch
     */
    private $productSearch;
    
    /**
     * Instância de LinkSearch
     * @var LinkSearch
     */
    private $linkSearch;
    
    /**
     * Instância de AdvertiserLookup
     * @var AdvertiserLookup
     */
    private $advertiserLookup;
    
    /**
     * Instância de CommissionDetail
     * @var CommissionDetail
     */
    private $commissionDetail;
    
    /**
     * Instância de ProgramTerms
     * @var ProgramTerms
     */
    private $programTerms;
    
    /**
     * Instância de PromotionalProperties
     * @var PromotionalProperties
     */
    private $promotionalProperties;
    
    /**
     * Instância de OfferFeed
     * @var OfferFeed
     */
    private $offerFeed;
    
    /**
     * Construtor
     * 
     * @param array $config Configurações do cliente
     * @throws CJException
     * 
     * Required configuration:
     * - access_token (string): Your CJ Personal Access Token
     * - publisher_id (string): Your Publisher CID
     * - website_id (string): Your Website ID (used as PID for affiliate links)
     * 
     * Optional configuration:
     * - timeout (int): Request timeout in seconds (default: 30)
     * - cache_enabled (bool): Enable caching (default: false)
     * - cache_ttl (int): Cache TTL in seconds (default: 3600)
     * - debug (bool): Enable debug mode (default: false)
     */
    public function __construct(array $config)
    {
        // Validate required configuration
        if (empty($config['access_token'])) {
            throw new CJException('Access token is required');
        }
        
        if (empty($config['publisher_id'])) {
            throw new CJException('Publisher ID is required');
        }
        
        if (empty($config['website_id'])) {
            throw new CJException('Website ID is required');
        }
        
        $this->accessToken = $config['access_token'];
        $this->publisherId = $config['publisher_id'];
        
        // Configurações padrão
        $defaults = [
            'timeout' => 30,
            'cache_enabled' => false,
            'cache_ttl' => 3600,
            'debug' => false
        ];
        
        $this->config = array_merge($defaults, $config);
    }
    
    /**
     * Retorna o access token
     * 
     * @return string
     */
    public function getAccessToken(): string
    {
        return $this->accessToken;
    }
    
    /**
     * Retorna o publisher ID
     * 
     * @return string
     */
    public function getPublisherId(): string
    {
        return $this->publisherId;
    }
    
    /**
     * Retorna o website ID (usado como PID)
     * 
     * @return string
     */
    public function getWebsiteId(): string
    {
        return $this->config['website_id'];
    }
    
    /**
     * Retorna configuração específica
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getConfig(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }
    
    /**
     * Retorna instância de ProductSearch
     * 
     * @return ProductSearch
     */
    public function products(): ProductSearch
    {
        if (!$this->productSearch) {
            $this->productSearch = new ProductSearch($this);
        }
        
        return $this->productSearch;
    }
    
    /**
     * Retorna instância de LinkSearch
     * 
     * @return LinkSearch
     */
    public function links(): LinkSearch
    {
        if (!$this->linkSearch) {
            $this->linkSearch = new LinkSearch($this);
        }
        
        return $this->linkSearch;
    }
    
    /**
     * Retorna instância de AdvertiserLookup
     * 
     * @return AdvertiserLookup
     */
    public function advertisers(): AdvertiserLookup
    {
        if (!$this->advertiserLookup) {
            $this->advertiserLookup = new AdvertiserLookup($this);
        }
        
        return $this->advertiserLookup;
    }
    
    /**
     * Retorna instância de CommissionDetail
     * 
     * @return CommissionDetail
     */
    public function commissions(): CommissionDetail
    {
        if (!$this->commissionDetail) {
            $this->commissionDetail = new CommissionDetail($this);
        }
        
        return $this->commissionDetail;
    }
    
    /**
     * Retorna instância de ProgramTerms
     * 
     * @return ProgramTerms
     */
    public function programTerms(): ProgramTerms
    {
        if (!$this->programTerms) {
            $this->programTerms = new ProgramTerms($this);
        }
        
        return $this->programTerms;
    }
    
    /**
     * Retorna instância de PromotionalProperties
     * 
     * @return PromotionalProperties
     */
    public function promotionalProperties(): PromotionalProperties
    {
        if (!$this->promotionalProperties) {
            $this->promotionalProperties = new PromotionalProperties($this);
        }
        
        return $this->promotionalProperties;
    }
    
    /**
     * Retorna instância de OfferFeed
     * 
     * @return OfferFeed
     */
    public function offers(): OfferFeed
    {
        if (!$this->offerFeed) {
            $this->offerFeed = new OfferFeed($this);
        }
        
        return $this->offerFeed;
    }
}
