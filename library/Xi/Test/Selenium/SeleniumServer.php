<?php
namespace Xi\Test\Selenium;

/**
 * Manages a connection to a Selenium server.
 * 
 * Most often the server will talk with `http://localhost:4444/wd/hub`,
 * but a remote Selenium server can be used just the same.
 * 
 * This server class is not a presistent connection. Instead each call
 * becomes a new CURL request. For this reason, this object doesn't need to
 * be explicitly "closed".
 * 
 * 
 * Thanks to http://code.google.com/p/php-webdriver-bindings/ for inspiration
 * and for a point of reference for interfacing problems and curl usage.
 */
class SeleniumServer
{
    protected $serverUrl;
    protected $lastRedirectLocation;
    protected $debuggingEnabled;
    
    public function __construct($serverUrl)
    {
        $this->serverUrl = rtrim($serverUrl, '/');
        $this->lastRedirectLocation = null;
        $this->debuggingEnabled = false;
    }
    
    public function enableDebug($enable = true)
    {
        $this->debuggingEnabled = $enable;
    }
    
    public function get($path)
    {
        return $this->doRequest($path, array(
            CURLOPT_HTTPGET => true
        ));
    }
    
    public function post($path, $params = null)
    {
        if ($params === null) {
            $params = array();
        }
        return $this->doRequest($path, array(
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($params)
        ));
    }
    
    public function delete($path)
    {
        return $this->doRequest($path, array(
            CURLOPT_CUSTOMREQUEST => 'DELETE'
        ));
    }
    
    protected function doRequest($path, $curlOpts)
    {
        $curl = curl_init();
        
        try {
            $defaultOpts = array(
                CURLOPT_HTTPHEADER => array("application/json; charset=UTF-8"),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => false,
                CURLOPT_HEADER => true,
                CURLOPT_VERBOSE => $this->debuggingEnabled
            );
            curl_setopt_array($curl, $defaultOpts);
            curl_setopt_array($curl, $curlOpts); // (can't array_merge because integer keys)
            
            if (strpos($path, $this->serverUrl) !== false) {
                $url = $path;
            } else {
                $url = $this->serverUrl . '/' . ltrim($path, '/');
            }
            curl_setopt($curl, CURLOPT_URL, $url);
            
            $response = curl_exec($curl);
            if ($response === false) {
                throw new \Exception("Failed to connect to Selenium server: " . curl_error($curl));
            }
            
            list($headers, $response) = $this->splitHeadersAndBody($response);
            
            $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $statusCodeCategory = (int)substr($statusCode, 0, 1);
            
            if ($statusCodeCategory == 3) {
                $this->lastRedirectLocation = $headers['Location'];
            } elseif ($statusCodeCategory == 4) {
                throw new \Exception("Selenium returned HTTP $statusCode: $response");
            }
            
            if (strpos(curl_getinfo($curl, CURLINFO_CONTENT_TYPE), 'json') !== false) {
                $response = json_decode(trim($response), true);
                if ($response['status'] != 0) {
                    $message = isset($response['message']) ? $response['message'] : null;
                    throw new SeleniumException($message, $response['status']);
                }
                $response = isset($response['value']) ? $response['value'] : null;
            }
            
            curl_close($curl);
            
        } catch (\Exception $e) {
            curl_close($curl);
            throw $e;
        }
        
        return $response;
    }
    
    public function splitHeadersAndBody($response)
    {
        if (strpos($response, "\r\n\r\n") !== false) {
            $separator = "\r\n\r\n";
        } else {
            $separator = "\n\n";
        }
        
        $parts = explode($separator, $response, 2);
        if (count($parts) == 1) {
            $headerLines = $parts[0];
            $body = null;
        } else {
            list($headerLines, $body) = $parts;
        }
        
        $headerLines = explode("\n", $headerLines);
        array_shift($headerLines); // The HTTP response code line
        $headerLines = array_map('trim', $headerLines);
        $headerLines = array_filter($headerLines, function($line) { return !empty($line); });
        
        $headers = array();
        foreach ($headerLines as $headerLine) {
            list($key, $value) = explode(':', $headerLine, 2);
            $headers[$key] = trim($value);
        }
        
        return array($headers, $body);
    }
    
    public function getLastRedirectLocation()
    {
        return $this->lastRedirectLocation;
    }
}