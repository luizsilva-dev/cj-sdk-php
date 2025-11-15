<?php
/**
 * Autoloader para CJ Affiliate SDK
 * 
 * Sistema de autoload simples para carregar classes automaticamente
 */

spl_autoload_register(function ($class) {
    // Namespace base do projeto
    $prefix = 'CJAffiliate\\';
    
    // Diretório base onde as classes estão
    $base_dir = __DIR__ . '/src/';
    
    // Verifica se a classe usa o namespace do projeto
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // Se não usa o namespace, não faz nada
        return;
    }
    
    // Remove o namespace base e obtém o nome relativo da classe
    $relative_class = substr($class, $len);
    
    // Substitui namespace separators por directory separators
    // e adiciona .php no final
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    // Se o arquivo existe, carrega
    if (file_exists($file)) {
        require $file;
    }
});
