<?php

namespace CJAffiliate\API;

use CJAffiliate\CJClient;
use CJAffiliate\Utils\HttpClient;
use CJAffiliate\Utils\Cache;
use CJAffiliate\Exceptions\CJException;

/**
 * API de Propriedades Promocionais CJ (GraphQL)
 * 
 * Gerencia PIDs (Publisher IDs) e propriedades promocionais
 * 
 * @package CJAffiliate\API
 */
class PromotionalProperties
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
     * Listar propriedades promocionais
     * 
     * @return array
     * @throws CJException
     */
    public function list(): array
    {
        $publisherId = $this->client->getPublisherId();
        
        $query = sprintf(
            '{
                promotionalProperties(publisherId: "%s") {
                    totalCount
                    resultList {
                        pid
                        name
                        description
                        websiteUrl
                        status
                        createdDate
                    }
                }
            }',
            $publisherId
        );
        
        return $this->executeGraphQL($query);
    }
    
    /**
     * Criar nova propriedade promocional
     * 
     * @param array $data
     * @return array
     * @throws CJException
     */
    public function create(array $data): array
    {
        $publisherId = $this->client->getPublisherId();
        $name = $data['name'];
        $description = $data['description'] ?? '';
        $websiteUrl = $data['website_url'] ?? '';
        
        $mutation = sprintf(
            'mutation {
                createPromotionalProperty(
                    publisherId: "%s",
                    name: "%s",
                    description: "%s",
                    websiteUrl: "%s"
                ) {
                    pid
                    name
                    status
                }
            }',
            $publisherId,
            $name,
            $description,
            $websiteUrl
        );
        
        return $this->executeGraphQL($mutation);
    }
    
    /**
     * Atualizar propriedade promocional
     * 
     * @param string $pid
     * @param array $data
     * @return array
     * @throws CJException
     */
    public function update(string $pid, array $data): array
    {
        $fields = [];
        
        if (isset($data['name'])) {
            $fields[] = sprintf('name: "%s"', $data['name']);
        }
        
        if (isset($data['description'])) {
            $fields[] = sprintf('description: "%s"', $data['description']);
        }
        
        if (isset($data['website_url'])) {
            $fields[] = sprintf('websiteUrl: "%s"', $data['website_url']);
        }
        
        $fieldsStr = implode(', ', $fields);
        
        $mutation = sprintf(
            'mutation {
                updatePromotionalProperty(
                    pid: "%s",
                    %s
                ) {
                    pid
                    name
                    status
                }
            }',
            $pid,
            $fieldsStr
        );
        
        return $this->executeGraphQL($mutation);
    }
    
    /**
     * Deletar propriedade promocional
     * 
     * @param string $pid
     * @return array
     * @throws CJException
     */
    public function delete(string $pid): array
    {
        $mutation = sprintf(
            'mutation {
                deletePromotionalProperty(pid: "%s") {
                    success
                    message
                }
            }',
            $pid
        );
        
        return $this->executeGraphQL($mutation);
    }
    
    private function executeGraphQL(string $query): array
    {
        try {
            $response = $this->http->post(self::GRAPHQL_ENDPOINT, [
                'query' => $query
            ], true);
            
            return $response;
            
        } catch (CJException $e) {
            throw $e;
        }
    }
}
