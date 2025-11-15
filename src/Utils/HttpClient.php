<?php

namespace CJAffiliate\Utils;

use CJAffiliate\Exceptions\CJException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;

/**
 * HTTP client for CJ API requests using Guzzle
 * 
 * @package CJAffiliate\Utils
 */
class HttpClient
{
    /**
     * Access token for authentication
     * @var string
     */
    private $accessToken;
    
    /**
     * Timeout in seconds
     * @var int
     */
    private $timeout;
    
    /**
     * Debug mode
     * @var bool
     */
    private $debug;
    
    /**
     * Guzzle client instance
     * @var Client
     */
    private $client;
    
    /**
     * Constructor
     * 
     * @param string $accessToken
     * @param int $timeout
     * @param bool $debug
     */
    public function __construct(string $accessToken, int $timeout = 30, bool $debug = false)
    {
        // Validate and clean access token
        $accessToken = trim($accessToken);
        
        if (empty($accessToken)) {
            throw new CJException('Access token cannot be empty');
        }
        
        $this->accessToken = $accessToken;
        $this->timeout = $timeout;
        $this->debug = $debug;
        
        // Initialize Guzzle client
        $config = [
            'timeout' => $timeout,
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Accept' => '*/*',  // Accept any content type
                'User-Agent' => 'CJ-SDK-PHP/1.0.0'
            ],
            'http_errors' => false, // We'll handle errors manually
            'verify' => true  // Verify SSL certificates
        ];
        
        // Only enable Guzzle debug if we can write to a proper stream
        if ($debug) {
            // Use error_log instead of Guzzle's native debug to avoid stream issues
            $config['on_stats'] = function ($stats) {
                error_log('[CJ SDK] Request: ' . $stats->getRequest()->getMethod() . ' ' . $stats->getRequest()->getUri());
                error_log('[CJ SDK] Response: ' . $stats->getResponse()->getStatusCode());
            };
        }
        
        $this->client = new Client($config);
    }
    
    /**
     * Make GET request
     * 
     * @param string $url
     * @param array $params
     * @return array
     * @throws CJException
     */
    public function get(string $url, array $params = []): array
    {
        if ($this->debug) {
            $this->log('GET Request', [
                'url' => $url,
                'params' => $params
            ]);
        }
        
        try {
            $response = $this->client->get($url, [
                'query' => $params
            ]);
            
            if ($this->debug) {
                $this->log('Response', [
                    'status' => $response->getStatusCode(),
                    'content_type' => $response->getHeaderLine('Content-Type'),
                    'body_length' => strlen((string)$response->getBody())
                ]);
            }
            
            return $this->handleResponse($response);
            
        } catch (RequestException $e) {
            if ($this->debug) {
                $this->log('Request Exception', [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode()
                ]);
            }
            throw $this->handleException($e);
        } catch (GuzzleException $e) {
            if ($this->debug) {
                $this->log('Guzzle Exception', [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode()
                ]);
            }
            throw new CJException(
                "HTTP request failed: " . $e->getMessage(),
                $e->getCode()
            );
        }
    }
    
    /**
     * Make POST request
     * 
     * @param string $url
     * @param array $data
     * @param bool $isJson
     * @return array
     * @throws CJException
     */
    public function post(string $url, array $data = [], bool $isJson = true): array
    {
        if ($this->debug) {
            $this->log('POST Request', [
                'url' => $url,
                'is_json' => $isJson,
                'data_keys' => array_keys($data)
            ]);
        }
        
        try {
            $options = [];
            
            if ($isJson) {
                $options['json'] = $data;
            } else {
                $options['form_params'] = $data;
            }
            
            $response = $this->client->post($url, $options);
            
            if ($this->debug) {
                $this->log('Response', [
                    'status' => $response->getStatusCode(),
                    'content_type' => $response->getHeaderLine('Content-Type'),
                    'body_length' => strlen((string)$response->getBody())
                ]);
            }
            
            return $this->handleResponse($response);
            
        } catch (RequestException $e) {
            if ($this->debug) {
                $this->log('Request Exception', [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode()
                ]);
            }
            throw $this->handleException($e);
        } catch (GuzzleException $e) {
            if ($this->debug) {
                $this->log('Guzzle Exception', [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode()
                ]);
            }
            throw new CJException(
                "HTTP request failed: " . $e->getMessage(),
                $e->getCode()
            );
        }
    }
    
    /**
     * Handle response from API
     * 
     * @param \Psr\Http\Message\ResponseInterface $response
     * @return array
     * @throws CJException
     */
    private function handleResponse($response): array
    {
        $statusCode = $response->getStatusCode();
        $body = (string) $response->getBody();
        $contentType = $response->getHeaderLine('Content-Type');
        
        // Check if response is empty
        if (empty($body)) {
            throw new CJException(
                "Empty response from API (HTTP {$statusCode}). Please check your credentials and API permissions.",
                $statusCode,
                [
                    'http_code' => $statusCode,
                    'content_type' => $contentType,
                    'headers' => $response->getHeaders()
                ]
            );
        }
        
        // Check HTTP status code
        if ($statusCode < 200 || $statusCode >= 300) {
            // Try to decode error response
            $errorData = json_decode($body, true);
            
            if (json_last_error() === JSON_ERROR_NONE && is_array($errorData)) {
                $message = $errorData['message'] 
                    ?? $errorData['error'] 
                    ?? $errorData['error_description'] 
                    ?? 'Request failed';
            } else {
                // If not JSON, use raw response
                $message = strlen($body) > 200 ? substr($body, 0, 200) . '...' : $body;
            }
            
            // Add specific guidance for common HTTP errors
            $guidance = '';
            if ($statusCode === 400) {
                // Check for authorization error
                if (stripos($message, 'not authorized') !== false && stripos($message, 'CID') !== false) {
                    $guidance = ' Your access token was not generated for this Publisher ID. To fix: 1) Verify your Publisher ID at CJ Members portal (top right corner), 2) Generate a NEW token at https://developers.cj.com/ while logged in with the CORRECT account, 3) Make sure the account that generated the token matches your Publisher ID.';
                } else {
                    $guidance = ' Invalid parameters or request format. Check the error message for details.';
                }
            } elseif ($statusCode === 401) {
                $guidance = ' Your access token is invalid or expired. Generate a new token at https://developers.cj.com/';
            } elseif ($statusCode === 403) {
                $guidance = ' Your account may not have API access or permission. Contact CJ Affiliate support.';
            } elseif ($statusCode === 404) {
                $guidance = ' The API endpoint was not found. Please check the SDK version.';
            } elseif ($statusCode === 406) {
                $guidance = ' The API rejected the request format. This may indicate: 1) Invalid access token format, 2) Missing required headers, or 3) API version mismatch.';
            } elseif ($statusCode >= 500) {
                $guidance = ' CJ API is experiencing issues. Please try again later.';
            }
            
            throw new CJException(
                "HTTP {$statusCode}: {$message}{$guidance}",
                $statusCode,
                [
                    'raw_response' => strlen($body) > 500 ? substr($body, 0, 500) . '...' : $body,
                    'content_type' => $contentType
                ]
            );
        }
        
        // Try to decode response based on content type
        if (stripos($contentType, 'xml') !== false || stripos($body, '<?xml') === 0) {
            // Parse XML response
            return $this->parseXmlResponse($body);
        }
        
        // Decode JSON response
        $data = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new CJException(
                "Failed to decode response: " . json_last_error_msg() . " (Content-Type: {$contentType})",
                0,
                [
                    'raw_response' => strlen($body) > 500 ? substr($body, 0, 500) . '...' : $body,
                    'content_type' => $contentType,
                    'http_code' => $statusCode
                ]
            );
        }
        
        return $data;
    }
    
    /**
     * Handle Guzzle exception
     * 
     * @param RequestException $e
     * @return CJException
     */
    private function handleException(RequestException $e): CJException
    {
        $response = $e->getResponse();
        
        if ($response) {
            $statusCode = $response->getStatusCode();
            $body = (string) $response->getBody();
            
            $errorData = json_decode($body, true);
            
            if (json_last_error() === JSON_ERROR_NONE && is_array($errorData)) {
                $message = $errorData['message'] 
                    ?? $errorData['error'] 
                    ?? $e->getMessage();
            } else {
                $message = $e->getMessage();
            }
            
            return new CJException(
                "HTTP {$statusCode}: {$message}",
                $statusCode,
                ['raw_response' => $body]
            );
        }
        
        return new CJException(
            "Request failed: " . $e->getMessage(),
            $e->getCode()
        );
    }
    
    /**
     * Check if response is valid JSON
     * 
     * @param string $response
     * @return bool
     */
    private function isJson(string $response): bool
    {
        json_decode($response);
        return json_last_error() === JSON_ERROR_NONE;
    }
    
    /**
     * Parse XML response to array
     * 
     * @param string $xml
     * @return array
     * @throws CJException
     */
    private function parseXmlResponse(string $xml): array
    {
        // Disable libxml errors
        libxml_use_internal_errors(true);
        
        // Parse XML with LIBXML_NOCDATA to handle CDATA sections
        $xmlObj = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        
        if ($xmlObj === false) {
            $errors = libxml_get_errors();
            libxml_clear_errors();
            
            $errorMsg = 'Failed to parse XML';
            if (!empty($errors)) {
                $errorMsg .= ': ' . $errors[0]->message;
            }
            
            throw new CJException($errorMsg, 0, ['raw_response' => substr($xml, 0, 500)]);
        }
        
        // Convert to array recursively with attributes
        $array = $this->xmlToArray($xmlObj);
        
        return $array ?: [];
    }
    
    /**
     * Convert XML object to array recursively
     * 
     * @param \SimpleXMLElement $xml
     * @return array
     */
    private function xmlToArray($xml): array
    {
        $array = [];
        
        // Get attributes
        foreach ($xml->attributes() as $key => $value) {
            $array['@' . $key] = (string) $value;
        }
        
        // Get child elements
        foreach ($xml as $key => $value) {
            $key = (string) $key;
            
            // If element has children or attributes
            if ($value->count() > 0 || $value->attributes()->count() > 0) {
                $converted = $this->xmlToArray($value);
            } else {
                $converted = (string) $value;
            }
            
            // Handle multiple elements with same name
            if (isset($array[$key])) {
                if (!is_array($array[$key]) || !isset($array[$key][0])) {
                    $array[$key] = [$array[$key]];
                }
                $array[$key][] = $converted;
            } else {
                $array[$key] = $converted;
            }
        }
        
        // If no attributes and no children, return the text content
        if (empty($array) && !$xml->count()) {
            return (string) $xml;
        }
        
        return $array;
    }
    
    /**
     * Log debug information
     * 
     * @param string $message
     * @param array $context
     */
    private function log(string $message, array $context = []): void
    {
        $logMessage = '[CJ SDK] ' . $message;
        
        if (!empty($context)) {
            $logMessage .= ' - ' . json_encode($context, JSON_UNESCAPED_SLASHES);
        }
        
        error_log($logMessage);
    }
}
