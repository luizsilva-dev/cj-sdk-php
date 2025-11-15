<?php

namespace CJAffiliate\API;

use CJAffiliate\CJClient;
use CJAffiliate\Utils\HttpClient;
use CJAffiliate\Utils\Cache;
use CJAffiliate\Exceptions\CJException;

/**
 * API de Detalhes de Comissão CJ (GraphQL)
 * 
 * Fornece dados de comissão em tempo quase real para publishers e advertisers
 * 
 * @package CJAffiliate\API
 */
class CommissionDetail
{
    /**
     * Endpoint GraphQL da CJ para Comissões
     */
    private const GRAPHQL_ENDPOINT = 'https://commissions.api.cj.com/query';
    
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
     * Busca comissões de publisher
     * 
     * @param array $params
     * @return array
     * @throws CJException
     */
    public function getPublisherCommissions(array $params): array
    {
        $publisherId = $params['publisher_id'] ?? $this->client->getPublisherId();
        $startDate = $params['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $params['end_date'] ?? date('Y-m-d');
        
        $query = sprintf(
            '{
                publisherCommissions(
                    forPublishers: ["%s"],
                    dateRange: {startDate: "%s", endDate: "%s"}
                ) {
                    totalCount
                    records {
                        commissionId
                        actionDate
                        eventDate
                        orderId
                        advertiserId
                        advertiserName
                        commissionAmount
                        saleAmount
                        pubCommissionAmountUsd
                        actionStatus
                        actionType
                        websiteName
                        postingDate
                    }
                }
            }',
            $publisherId,
            $startDate,
            $endDate
        );
        
        return $this->executeGraphQL($query);
    }
    
    /**
     * Busca comissões por advertiser
     * 
     * @param array $params
     * @return array
     * @throws CJException
     */
    public function getAdvertiserCommissions(array $params): array
    {
        $advertiserId = $params['advertiser_id'];
        $startDate = $params['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $params['end_date'] ?? date('Y-m-d');
        
        $query = sprintf(
            '{
                advertiserCommissions(
                    forAdvertisers: ["%s"],
                    dateRange: {startDate: "%s", endDate: "%s"}
                ) {
                    totalCount
                    records {
                        commissionId
                        actionDate
                        orderId
                        publisherId
                        publisherName
                        commissionAmount
                        saleAmount
                        actionStatus
                        actionType
                    }
                }
            }',
            $advertiserId,
            $startDate,
            $endDate
        );
        
        return $this->executeGraphQL($query);
    }
    
    /**
     * Obter resumo de comissões
     * 
     * @param array $params
     * @return array
     * @throws CJException
     */
    public function getSummary(array $params = []): array
    {
        $publisherId = $params['publisher_id'] ?? $this->client->getPublisherId();
        $startDate = $params['start_date'] ?? date('Y-m-01'); // Primeiro dia do mês
        $endDate = $params['end_date'] ?? date('Y-m-d');
        
        $commissions = $this->getPublisherCommissions([
            'publisher_id' => $publisherId,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
        
        $summary = [
            'total_commissions' => 0,
            'total_amount' => 0,
            'by_status' => [],
            'by_advertiser' => [],
            'period' => [
                'start' => $startDate,
                'end' => $endDate
            ]
        ];
        
        if (isset($commissions['data']['publisherCommissions']['records'])) {
            foreach ($commissions['data']['publisherCommissions']['records'] as $record) {
                $summary['total_commissions']++;
                $summary['total_amount'] += $record['pubCommissionAmountUsd'] ?? 0;
                
                $status = $record['actionStatus'];
                $summary['by_status'][$status] = ($summary['by_status'][$status] ?? 0) + 1;
                
                $advertiser = $record['advertiserName'];
                if (!isset($summary['by_advertiser'][$advertiser])) {
                    $summary['by_advertiser'][$advertiser] = [
                        'count' => 0,
                        'amount' => 0
                    ];
                }
                $summary['by_advertiser'][$advertiser]['count']++;
                $summary['by_advertiser'][$advertiser]['amount'] += $record['pubCommissionAmountUsd'] ?? 0;
            }
        }
        
        return $summary;
    }
    
    /**
     * Executa query GraphQL
     */
    private function executeGraphQL(string $query): array
    {
        $cacheKey = 'commission_' . md5($query);
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
