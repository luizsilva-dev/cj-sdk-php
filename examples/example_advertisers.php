<?php
/**
 * Exemplo: Busca de Anunciantes CJ Affiliate
 * 
 * Este exemplo demonstra como buscar anunciantes/partners
 */

require_once __DIR__ . '/../autoload.php';

use CJAffiliate\CJClient;
use CJAffiliate\Exceptions\CJException;

// Configurações (substitua com suas credenciais)
$config = [
    'access_token' => 'SEU_PERSONAL_ACCESS_TOKEN',
    'publisher_id' => 'SEU_PUBLISHER_ID'
];

try {
    // Inicializa cliente
    $client = new CJClient($config);
    
    echo "=== Busca de Anunciantes CJ Affiliate ===\n\n";
    
    // Exemplo 1: Listar todos os anunciantes com relacionamento ativo
    echo "1. Listando anunciantes com relacionamento ativo (joined):\n";
    echo str_repeat('-', 50) . "\n";
    
    $joinedAdvertisers = $client->advertisers()->getJoined([
        'records-per-page' => 10
    ]);
    
    echo "Total de anunciantes ativos: {$joinedAdvertisers['total_matched']}\n\n";
    
    foreach ($joinedAdvertisers['advertisers'] as $index => $advertiser) {
        echo "Anunciante #" . ($index + 1) . ":\n";
        echo "  Nome: {$advertiser['advertiser_name']}\n";
        echo "  ID: {$advertiser['advertiser_id']}\n";
        echo "  Status: {$advertiser['relationship_status']}\n";
        echo "  Categoria: {$advertiser['primary_category']}\n";
        echo "  Ranking na Rede: {$advertiser['network_rank']}\n";
        echo "  EPC (7 dias): {$advertiser['seven_day_epc']}\n";
        echo "  EPC (3 meses): {$advertiser['three_month_epc']}\n";
        echo "  URL do Programa: {$advertiser['program_url']}\n";
        echo "\n";
    }
    
    // Exemplo 2: Buscar anunciantes por palavra-chave
    echo "\n2. Buscando anunciantes por palavra-chave 'fashion':\n";
    echo str_repeat('-', 50) . "\n";
    
    $fashionAdvertisers = $client->advertisers()->search([
        'keywords' => 'fashion',
        'records-per-page' => 5
    ]);
    
    echo "Anunciantes encontrados: {$fashionAdvertisers['total_matched']}\n\n";
    
    foreach ($fashionAdvertisers['advertisers'] as $advertiser) {
        echo "- {$advertiser['advertiser_name']} (Status: {$advertiser['relationship_status']})\n";
    }
    
    // Exemplo 3: Buscar anunciantes por categoria
    echo "\n3. Buscando anunciantes por categoria 'Electronics':\n";
    echo str_repeat('-', 50) . "\n";
    
    $electronicsAdvertisers = $client->advertisers()->getByCategory('Electronics', [
        'relationship-status' => 'joined',
        'records-per-page' => 5
    ]);
    
    echo "Anunciantes na categoria Electronics: {$electronicsAdvertisers['total_matched']}\n\n";
    
    foreach ($electronicsAdvertisers['advertisers'] as $advertiser) {
        echo "- {$advertiser['advertiser_name']}\n";
        echo "  EPC (7 dias): \${$advertiser['seven_day_epc']}\n";
        echo "  Ações disponíveis: " . count($advertiser['actions']) . "\n\n";
    }
    
    // Exemplo 4: Buscar anunciante específico por ID
    echo "\n4. Buscando anunciante específico por ID:\n";
    echo str_repeat('-', 50) . "\n";
    
    $advertiser = $client->advertisers()->getById('123456');
    
    if ($advertiser) {
        echo "Anunciante encontrado:\n";
        echo "  Nome: {$advertiser['advertiser_name']}\n";
        echo "  ID: {$advertiser['advertiser_id']}\n";
        echo "  Status do Relacionamento: {$advertiser['relationship_status']}\n";
        echo "  Categoria Primária: {$advertiser['primary_category']}\n";
        echo "  Idioma: {$advertiser['language']}\n";
        echo "  Possui Incentivos de Performance: " . 
               ($advertiser['performance_incentives'] ? 'Sim' : 'Não') . "\n";
    } else {
        echo "Anunciante não encontrado.\n";
    }
    
    // Exemplo 5: Anunciantes com melhor performance
    echo "\n5. Listando anunciantes com melhor EPC (3 meses):\n";
    echo str_repeat('-', 50) . "\n";
    
    $allAdvertisers = $client->advertisers()->getJoined([
        'records-per-page' => 50
    ]);
    
    // Ordena por EPC de 3 meses
    $sortedAdvertisers = $allAdvertisers['advertisers'];
    usort($sortedAdvertisers, function($a, $b) {
        return $b['three_month_epc'] <=> $a['three_month_epc'];
    });
    
    // Mostra top 10
    $top10 = array_slice($sortedAdvertisers, 0, 10);
    
    foreach ($top10 as $index => $advertiser) {
        echo ($index + 1) . ". {$advertiser['advertiser_name']}\n";
        echo "   EPC (3 meses): \${$advertiser['three_month_epc']}\n";
        echo "   Categoria: {$advertiser['primary_category']}\n\n";
    }
    
    // Exemplo 6: Estatísticas gerais
    echo "\n6. Estatísticas gerais dos anunciantes:\n";
    echo str_repeat('-', 50) . "\n";
    
    $allStats = $client->advertisers()->search([
        'records-per-page' => 1000
    ]);
    
    $joined = 0;
    $notJoined = 0;
    $categories = [];
    
    foreach ($allStats['advertisers'] as $adv) {
        if ($adv['relationship_status'] === 'joined') {
            $joined++;
        } else {
            $notJoined++;
        }
        
        if ($adv['primary_category']) {
            $categories[$adv['primary_category']] = 
                ($categories[$adv['primary_category']] ?? 0) + 1;
        }
    }
    
    echo "Total de anunciantes: {$allStats['total_matched']}\n";
    echo "Com relacionamento ativo: {$joined}\n";
    echo "Sem relacionamento: {$notJoined}\n\n";
    
    echo "Top 5 categorias:\n";
    arsort($categories);
    $topCategories = array_slice($categories, 0, 5, true);
    
    foreach ($topCategories as $category => $count) {
        echo "  - {$category}: {$count} anunciantes\n";
    }
    
    echo "\n=== Busca concluída com sucesso! ===\n";
    
} catch (CJException $e) {
    echo "\nErro CJ Affiliate:\n";
    echo $e->getDetails();
} catch (\Exception $e) {
    echo "\nErro: " . $e->getMessage() . "\n";
}
