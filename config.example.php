<?php
/**
 * Exemplo de arquivo de configuração
 * 
 * Copie este arquivo para config.php e preencha com suas credenciais
 * IMPORTANTE: Adicione config.php ao .gitignore!
 */

return [
    /**
     * Personal Access Token da CJ
     * Obtenha em: https://developers.cj.com/account/personal-access-tokens
     */
    'access_token' => 'seu_personal_access_token_aqui',
    
    /**
     * Publisher ID
     * Encontre no canto superior direito do portal CJ Members
     */
    'publisher_id' => 'seu_publisher_id_aqui',
    
    /**
     * Website ID
     * Obtenha em: Account -> Websites no portal CJ
     */
    'website_id' => 'seu_website_id_aqui',
    
    /**
     * Habilitar cache
     * Recomendado: true em produção para melhor performance
     */
    'cache_enabled' => true,
    
    /**
     * Tempo de vida do cache em segundos
     * 3600 = 1 hora
     * 7200 = 2 horas
     * 86400 = 24 horas
     */
    'cache_ttl' => 3600,
    
    /**
     * Timeout das requisições HTTP em segundos
     */
    'timeout' => 30,
    
    /**
     * Modo debug
     * true = Exibe informações detalhadas de debug
     * false = Modo produção (recomendado)
     */
    'debug' => false
];
