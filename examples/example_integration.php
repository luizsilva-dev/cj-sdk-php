<?php
/**
 * Exemplo: Integração Completa para Plataforma de Afiliados
 * 
 * Este exemplo demonstra como usar o SDK para criar uma plataforma
 * de links de afiliados similar ao dealtrust.us
 */

require_once __DIR__ . '/../autoload.php';

use CJAffiliate\CJClient;
use CJAffiliate\Exceptions\CJException;

// Configurações
$config = [
    'access_token' => 'SEU_PERSONAL_ACCESS_TOKEN',
    'publisher_id' => 'SEU_PUBLISHER_ID',
    'cache_enabled' => true,
    'cache_ttl' => 7200 // 2 horas
];

/**
 * Função para buscar e processar produtos para exibição no site
 */
function getProductsForDisplay(CJClient $client, string $websiteId, string $category, int $limit = 20): array
{
    $products = $client->products()->search([
        'website_id' => $websiteId,
        'keywords' => $category,
        'records_per_page' => $limit,
        'serviceable_area' => 'US'
    ]);
    
    $displayProducts = [];
    
    foreach ($products['products'] as $product) {
        // Processa cada produto para exibição
        $displayProducts[] = [
            'id' => $product['id'],
            'title' => $product['name'],
            'description' => substr($product['description'], 0, 150) . '...',
            'price' => $product['price'],
            'sale_price' => $product['sale_price'],
            'currency' => $product['currency'],
            'image' => $product['image_url'],
            'affiliate_link' => $product['buy_url'], // Link já contém tracking
            'merchant' => $product['advertiser_name'],
            'in_stock' => $product['in_stock'],
            'brand' => $product['brand']
        ];
    }
    
    return $displayProducts;
}

/**
 * Função para obter ofertas/cupons
 */
function getDealsAndCoupons(CJClient $client, string $websiteId, int $limit = 20): array
{
    $links = $client->links()->search([
        'website-id' => $websiteId,
        'promotion-type' => 'coupon',
        'relationship-status' => 'joined',
        'records-per-page' => $limit
    ]);
    
    $deals = [];
    
    foreach ($links['links'] as $link) {
        if ($link['coupon_code']) {
            $deals[] = [
                'title' => $link['link_name'],
                'merchant' => $link['advertiser_name'],
                'coupon_code' => $link['coupon_code'],
                'description' => $link['description'],
                'link' => $link['destination'],
                'expires' => $link['promotion_end_date'],
                'commission' => $link['sale_commission']
            ];
        }
    }
    
    return $deals;
}

/**
 * Função para obter informações de merchants/lojas
 */
function getMerchantsList(CJClient $client, string $category = ''): array
{
    $params = [
        'relationship-status' => 'joined',
        'records-per-page' => 50
    ];
    
    if ($category) {
        $params['category'] = $category;
    }
    
    $advertisers = $client->advertisers()->search($params);
    
    $merchants = [];
    
    foreach ($advertisers['advertisers'] as $advertiser) {
        $merchants[] = [
            'id' => $advertiser['advertiser_id'],
            'name' => $advertiser['advertiser_name'],
            'category' => $advertiser['primary_category'],
            'program_url' => $advertiser['program_url'],
            'performance' => [
                'epc_7_days' => $advertiser['seven_day_epc'],
                'epc_3_months' => $advertiser['three_month_epc'],
                'network_rank' => $advertiser['network_rank']
            ],
            'has_incentives' => $advertiser['performance_incentives']
        ];
    }
    
    return $merchants;
}

/**
 * Função para gerar HTML de produto
 */
function generateProductHTML(array $product): string
{
    $html = '<div class="product-card">';
    $html .= '<img src="' . htmlspecialchars($product['image']) . '" alt="' . htmlspecialchars($product['title']) . '">';
    $html .= '<h3>' . htmlspecialchars($product['title']) . '</h3>';
    $html .= '<p class="description">' . htmlspecialchars($product['description']) . '</p>';
    $html .= '<p class="merchant">Vendido por: ' . htmlspecialchars($product['merchant']) . '</p>';
    
    if ($product['sale_price']) {
        $html .= '<p class="price">';
        $html .= '<span class="old-price">' . $product['currency'] . ' ' . $product['price'] . '</span> ';
        $html .= '<span class="sale-price">' . $product['currency'] . ' ' . $product['sale_price'] . '</span>';
        $html .= '</p>';
    } else {
        $html .= '<p class="price">' . $product['currency'] . ' ' . $product['price'] . '</p>';
    }
    
    $stockClass = $product['in_stock'] ? 'in-stock' : 'out-of-stock';
    $stockText = $product['in_stock'] ? 'Em Estoque' : 'Fora de Estoque';
    $html .= '<p class="stock ' . $stockClass . '">' . $stockText . '</p>';
    
    $html .= '<a href="' . htmlspecialchars($product['affiliate_link']) . '" class="btn-buy" target="_blank" rel="nofollow">';
    $html .= 'Ver Oferta →</a>';
    $html .= '</div>';
    
    return $html;
}

try {
    // Inicializa cliente
    $client = new CJClient($config);
    
    $websiteId = 'SEU_WEBSITE_ID';
    
    echo "=== Exemplo de Integração Completa ===\n\n";
    
    // 1. Buscar produtos por categoria
    echo "1. Buscando produtos da categoria 'Electronics':\n";
    echo str_repeat('-', 50) . "\n";
    
    $electronics = getProductsForDisplay($client, $websiteId, 'electronics', 10);
    
    echo "Produtos encontrados: " . count($electronics) . "\n\n";
    
    foreach (array_slice($electronics, 0, 3) as $product) {
        echo "Produto: {$product['title']}\n";
        echo "Preço: {$product['currency']} {$product['price']}\n";
        echo "Loja: {$product['merchant']}\n";
        echo "Link: {$product['affiliate_link']}\n\n";
    }
    
    // 2. Buscar ofertas e cupons
    echo "\n2. Buscando ofertas e cupons:\n";
    echo str_repeat('-', 50) . "\n";
    
    $deals = getDealsAndCoupons($client, $websiteId, 10);
    
    echo "Ofertas encontradas: " . count($deals) . "\n\n";
    
    foreach (array_slice($deals, 0, 3) as $deal) {
        echo "Oferta: {$deal['title']}\n";
        echo "Loja: {$deal['merchant']}\n";
        echo "Cupom: {$deal['coupon_code']}\n";
        echo "Comissão: {$deal['commission']}\n";
        if ($deal['expires']) {
            echo "Expira em: {$deal['expires']}\n";
        }
        echo "\n";
    }
    
    // 3. Listar merchants
    echo "\n3. Listando merchants da categoria 'Fashion':\n";
    echo str_repeat('-', 50) . "\n";
    
    $merchants = getMerchantsList($client, 'Fashion');
    
    echo "Merchants encontrados: " . count($merchants) . "\n\n";
    
    foreach (array_slice($merchants, 0, 5) as $merchant) {
        echo "Loja: {$merchant['name']}\n";
        echo "EPC (3 meses): \${$merchant['performance']['epc_3_months']}\n";
        echo "Ranking: {$merchant['performance']['network_rank']}\n\n";
    }
    
    // 4. Exemplo de geração de HTML
    echo "\n4. Exemplo de HTML gerado para produto:\n";
    echo str_repeat('-', 50) . "\n";
    
    if (count($electronics) > 0) {
        $html = generateProductHTML($electronics[0]);
        echo $html . "\n";
    }
    
    // 5. Buscar produtos similares/relacionados
    echo "\n5. Sistema de recomendação - Produtos similares:\n";
    echo str_repeat('-', 50) . "\n";
    
    if (count($electronics) > 0) {
        $mainProduct = $electronics[0];
        
        // Busca produtos similares baseado na marca
        $similar = $client->products()->search([
            'website_id' => $websiteId,
            'keywords' => $mainProduct['brand'],
            'records_per_page' => 5
        ]);
        
        echo "Produtos similares a '{$mainProduct['title']}':\n\n";
        
        foreach ($similar['products'] as $product) {
            echo "- {$product['name']} ({$product['currency']} {$product['price']})\n";
        }
    }
    
    echo "\n\n=== Integração demonstrada com sucesso! ===\n";
    echo "\nPróximos passos para sua plataforma:\n";
    echo "1. Criar banco de dados para armazenar produtos e cache\n";
    echo "2. Implementar sistema de categorias e filtros\n";
    echo "3. Adicionar sistema de busca no frontend\n";
    echo "4. Implementar tracking de clicks e conversões\n";
    echo "5. Criar dashboard para análise de performance\n";
    echo "6. Adicionar sistema de comparação de preços\n";
    echo "7. Implementar notificações de ofertas\n";
    
} catch (CJException $e) {
    echo "\nErro CJ Affiliate:\n";
    echo $e->getDetails();
} catch (\Exception $e) {
    echo "\nErro: " . $e->getMessage() . "\n";
}
