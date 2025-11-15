<?php

namespace CJAffiliate\Utils;

/**
 * Sistema simples de cache baseado em arquivos
 * 
 * @package CJAffiliate\Utils
 */
class Cache
{
    /**
     * Diretório de cache
     * @var string
     */
    private $cacheDir;
    
    /**
     * TTL padrão em segundos
     * @var int
     */
    private $ttl;
    
    /**
     * Cache habilitado
     * @var bool
     */
    private $enabled;
    
    /**
     * Construtor
     * 
     * @param bool $enabled
     * @param int $ttl
     * @param string|null $cacheDir
     */
    public function __construct(bool $enabled = false, int $ttl = 3600, ?string $cacheDir = null)
    {
        $this->enabled = $enabled;
        $this->ttl = $ttl;
        $this->cacheDir = $cacheDir ?? sys_get_temp_dir() . '/cj_affiliate_cache';
        
        // Cria diretório de cache se não existir
        if ($this->enabled && !is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }
    
    /**
     * Obtém valor do cache
     * 
     * @param string $key
     * @return mixed|null
     */
    public function get(string $key)
    {
        if (!$this->enabled) {
            return null;
        }
        
        $file = $this->getCacheFile($key);
        
        if (!file_exists($file)) {
            return null;
        }
        
        // Verifica se expirou
        if (time() - filemtime($file) > $this->ttl) {
            unlink($file);
            return null;
        }
        
        $data = file_get_contents($file);
        return unserialize($data);
    }
    
    /**
     * Define valor no cache
     * 
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function set(string $key, $value): bool
    {
        if (!$this->enabled) {
            return false;
        }
        
        $file = $this->getCacheFile($key);
        $data = serialize($value);
        
        return file_put_contents($file, $data) !== false;
    }
    
    /**
     * Remove item do cache
     * 
     * @param string $key
     * @return bool
     */
    public function delete(string $key): bool
    {
        if (!$this->enabled) {
            return false;
        }
        
        $file = $this->getCacheFile($key);
        
        if (file_exists($file)) {
            return unlink($file);
        }
        
        return false;
    }
    
    /**
     * Limpa todo o cache
     * 
     * @return bool
     */
    public function clear(): bool
    {
        if (!$this->enabled || !is_dir($this->cacheDir)) {
            return false;
        }
        
        $files = glob($this->cacheDir . '/*.cache');
        
        foreach ($files as $file) {
            unlink($file);
        }
        
        return true;
    }
    
    /**
     * Retorna caminho do arquivo de cache
     * 
     * @param string $key
     * @return string
     */
    private function getCacheFile(string $key): string
    {
        $hash = md5($key);
        return $this->cacheDir . '/' . $hash . '.cache';
    }
}
