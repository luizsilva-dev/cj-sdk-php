<?php

namespace CJAffiliate\Exceptions;

use Exception;

/**
 * Exceção customizada para erros do CJ Affiliate SDK
 * 
 * @package CJAffiliate\Exceptions
 */
class CJException extends Exception
{
    /**
     * Resposta HTTP completa (se disponível)
     * @var array|null
     */
    private $response;
    
    /**
     * Construtor
     * 
     * @param string $message Mensagem de erro
     * @param int $code Código de erro
     * @param array|null $response Resposta HTTP completa
     */
    public function __construct(string $message, int $code = 0, ?array $response = null)
    {
        parent::__construct($message, $code);
        $this->response = $response;
    }
    
    /**
     * Retorna a resposta HTTP completa
     * 
     * @return array|null
     */
    public function getResponse(): ?array
    {
        return $this->response;
    }
    
    /**
     * Retorna string formatada com detalhes do erro
     * 
     * @return string
     */
    public function getDetails(): string
    {
        $details = "Erro CJ Affiliate: {$this->getMessage()}\n";
        $details .= "Código: {$this->getCode()}\n";
        
        if ($this->response) {
            $details .= "Resposta HTTP:\n";
            $details .= json_encode($this->response, JSON_PRETTY_PRINT);
        }
        
        return $details;
    }
}
