<?php

namespace CJAffiliate\API;

use CJAffiliate\CJClient;
use CJAffiliate\Utils\HttpClient;
use CJAffiliate\Utils\Cache;
use CJAffiliate\Exceptions\CJException;

/**
 * API de Termos de Programa CJ (GraphQL)
 * 
 * Acessa taxas de comissão detalhadas do programa
 * 
 * @package CJAffiliate\API
 */
class ProgramTerms
{
    private const GRAPHQL_ENDPOINT = 'https://accounts.api.cj.com/graphql';
    
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
     * Obter termos do programa
     * 
     * @param string $advertiserId
     * @return array
     * @throws CJException
     */
    public function getProgramTerms(string $advertiserId): array
    {
        $publisherId = $this->client->getPublisherId();
        
        $query = sprintf(
            '{
                programTerms(
                    publisherId: "%s",
                    advertiserId: "%s"
                ) {
                    advertiserId
                    advertiserName
                    publisherId
                    situations {
                        situationId
                        situationName
                        commissionRate
                        commissionType
                    }
                    itemLists {
                        itemListId
                        itemListName
                        commissionRate
                    }
                }
            }',
            $publisherId,
            $advertiserId
        );
        
        return $this->executeGraphQL($query);
    }
    
    /**
     * Listar todos os programas
     * 
     * @return array
     * @throws CJException
     */
    public function listPrograms(): array
    {
        $publisherId = $this->client->getPublisherId();
        
        $query = sprintf(
            '{
                publisherPrograms(publisherId: "%s") {
                    totalCount
                    programs {
                        advertiserId
                        advertiserName
                        programStatus
                        relationshipStatus
                    }
                }
            }',
            $publisherId
        );
        
        return $this->executeGraphQL($query);
    }
    
    private function executeGraphQL(string $query): array
    {
        $cacheKey = 'program_terms_' . md5($query);
        $cached = $this->cache->get($cacheKey);
        
        if ($cached !== null) {
            return $cached;
        }
        
        try {
            $response = $this->http->post(self::GRAPHQL_ENDPOINT, [
                'query' => $query
            ], true);
            
            $this->cache->set($cacheKey, $response);
            return $response;
            
        } catch (CJException $e) {
            throw $e;
        }
    }
}
