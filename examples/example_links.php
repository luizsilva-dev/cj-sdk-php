<?php
/**
 * Exemplo: Busca de Links de Afiliados CJ
 * 
 * Este exemplo demonstra como buscar links de afiliados
 */

require_once __DIR__ . '/../autoload.php';

use CJAffiliate\CJClient;
use CJAffiliate\Exceptions\CJException;

// Configurações (substitua com suas credenciais)
$config = [
    'access_token' => 'SEU_PERSONAL_ACCESS_TOKEN',
    'publisher_id' => 'SEU_PUBLISHER_ID',
    'cache_enabled' => true
];

try {
    // Inicializa cliente
    $client = new CJClient($config);
    
    echo "=== Busca de Links de Afiliados CJ ===\n\n";
    
    // Exemplo 1: Busca simples de links
    echo "1. Buscando links de afiliados por palavra-chave:\n";
    echo str_repeat('-', 50) . "\n";
    
    $links = $client->links()->search([
        'website-id' => 'SEU_WEBSITE_ID', // Obrigatório
        'keywords' => 'shoes',
        'records-per-page' => 5
    ]);
    
    echo "Total de links encontrados: {$links['total_matched']}\n";
    echo "Links retornados: {$links['records_returned']}\n\n";
    
    foreach ($links['links'] as $index => $link) {
        echo "Link #" . ($index + 1) . ":\n";
        echo "  Nome: {$link['link_name']}\n";
        echo "  Tipo: {$link['link_type']}\n";
        echo "  Anunciante: {$link['advertiser_name']} (ID: {$link['advertiser_id']})\n";
        echo "  Comissão por Clique: {$link['click_commission']}\n";
        echo "  Comissão por Venda: {$link['sale_commission']}\n";
        echo "  Status: {$link['relationship_status']}\n";
        
        if ($link['coupon_code']) {
            echo "  Código de Cupom: {$link['coupon_code']}\n";
        }
        
        if ($link['description']) {
            $desc = substr($link['description'], 0, 80);
            echo "  Descrição: {$desc}...\n";
        }
        
        echo "  Código HTML:\n";
        echo "    " . htmlspecialchars(substr($link['link_code_html'], 0, 100)) . "...\n";
        echo "\n";
    }
    
    // Exemplo 2: Busca por anunciante específico
    echo "\n2. Buscando links de um anunciante específico:\n";
    echo str_repeat('-', 50) . "\n";
    
    $advertiserLinks = $client->links()->getByAdvertiser(
        'SEU_WEBSITE_ID',
        '123456', // ID do anunciante
        [
            'link-type' => 'Banner',
            'records-per-page' => 5
        ]
    );
    
    echo "Links do anunciante: {$advertiserLinks['records_returned']}\n\n";
    
    foreach ($advertiserLinks['links'] as $link) {
        echo "- {$link['link_name']} ({$link['link_type']})\n";
    }
    
    // Exemplo 3: Busca apenas links com relacionamento ativo
    echo "\n3. Buscando apenas links de anunciantes com relacionamento ativo:\n";
    echo str_repeat('-', 50) . "\n";
    
    $joinedLinks = $client->links()->search([
        'website-id' => 'SEU_WEBSITE_ID',
        'relationship-status' => 'joined',
        'keywords' => 'fashion',
        'records-per-page' => 10
    ]);
    
    echo "Links com relacionamento ativo: {$joinedLinks['total_matched']}\n\n";
    
    // Exemplo 4: Busca links com cupons
    echo "\n4. Buscando links que contêm cupons de desconto:\n";
    echo str_repeat('-', 50) . "\n";
    
    $couponLinks = $client->links()->search([
        'website-id' => 'SEU_WEBSITE_ID',
        'promotion-type' => 'coupon',
        'relationship-status' => 'joined',
        'records-per-page' => 10
    ]);
    
    echo "Links com cupons encontrados: {$couponLinks['total_matched']}\n\n";
    
    foreach ($couponLinks['links'] as $index => $link) {
        if ($link['coupon_code']) {
            echo "Link #" . ($index + 1) . ":\n";
            echo "  Nome: {$link['link_name']}\n";
            echo "  Anunciante: {$link['advertiser_name']}\n";
            echo "  Cupom: {$link['coupon_code']}\n";
            
            if ($link['promotion_end_date']) {
                echo "  Válido até: {$link['promotion_end_date']}\n";
            }
            
            echo "\n";
        }
    }
    
    // Exemplo 5: Diferentes tipos de links
    echo "\n5. Buscando diferentes tipos de links:\n";
    echo str_repeat('-', 50) . "\n";
    
    $linkTypes = ['Banner', 'Text Link', 'Advanced'];
    
    foreach ($linkTypes as $type) {
        $typedLinks = $client->links()->search([
            'website-id' => 'SEU_WEBSITE_ID',
            'link-type' => $type,
            'relationship-status' => 'joined',
            'records-per-page' => 3
        ]);
        
        echo "Tipo '{$type}': {$typedLinks['total_matched']} links disponíveis\n";
    }
    
    echo "\n=== Busca concluída com sucesso! ===\n";
    
} catch (CJException $e) {
    echo "\nErro CJ Affiliate:\n";
    echo $e->getDetails();
} catch (\Exception $e) {
    echo "\nErro: " . $e->getMessage() . "\n";
}
