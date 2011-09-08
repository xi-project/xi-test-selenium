<?php
namespace Xi\Test\Selenium;

/**
 * A Selenium WebDriver session.
 * 
 * This is the interface a test-writer should use to interact with Selenium.
 * 
 * Subclass this and adding your own convenience methods is fully supported.
 */
class WebDriver
{
    /**
     * @var SeleniumServer
     */
    protected $server;
    protected $options;
    protected $sessionPath;
    
    protected $baseUrl;
    
    /**
     * Constructs a WebDriver to use a given server.
     * 
     * The options may include any capabilities that the session opening
     * request of Selenium Server accepts. Most important of these are probably
     * 'browserName' and 'javascriptEnabled'.
     * 
     * @param SeleniumServer $server
     * @param array $options 
     */
    public function __construct(SeleniumServer $server, array $options = array())
    {
        $this->server = $server;
        $this->options = array_merge($this->getDefaultOptions(), $options);
        
        $this->openSession();
    }
    
    /**
     * Attempts to close the session, if possible.
     * 
     * Note that this is not called on fatal error,
     * and I'm afraid the garbage collection order when PHP exits might
     * cause this to fail randomly.
     */
    public function __destruct()
    {
        if ($this->server) {
            echo "Warning: The Selenium session was not explicitly closed!\n";
            echo "         Call closeSession() on your WebDriver instance.\n";
            try {
                $this->closeSession();
            } catch (Exception $e) {
                echo "(failed to close Selenium session)";
            }
        }
    }
    
    /**
     * Sets the baseUrl to use for relative paths.
     */
    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
    }
    
    public final function getBaseUrl()
    {
        return $this->baseUrl;
    }
    
    /**
     * Navigates to the given URL.
     * 
     * If the URL is relative, then the baseUrl (if any) is prepended.
     */
    public function visit($url)
    {
        $this->sessionPost('/url', array('url' => $this->filterUrl($url)));
    }
    
    /**
     * Returns the (full) contents of the browser's URL bar.
     * @return string
     */
    public function getUrl()
    {
        return $this->sessionGet('/url');
    }
    
    /**
     * Returns the page title.
     * @return string
     */
    public function getPageTitle()
    {
        return $this->sessionGet('/title');
    }
    
    /**
     * Returns the source code of the current page.
     * @return string
     */
    public function getPageSourceCode()
    {
        return $this->sessionGet('/source');
    }
    
    /**
     * Moves back one step on the browser history.
     */
    public function back()
    {
        $this->sessionPost('/back');
    }
    
    /**
     * Moves forward one step on the browser history.
     */
    public function forward()
    {
        $this->sessionPost('/forward');
    }
    
    /**
     * Finds an element by a CSS selector.
     * 
     * @param string $cssSelector A CSS selector.
     * @return WebElement The matched element. Never null.
     * @throws SeleniumException if an error occurred or no element matched
     */
    public function findElement($cssSelector)
    {
        $response = $this->sessionPost('/element', array('using' => 'css selector', 'value' => $cssSelector));
        return $this->createWebElement($response['ELEMENT']);
    }
    
    /**
     * Tries to find an element by a CSS selector.
     * 
     * @param string $cssSelector A CSS selector.
     * @return WebElement The matched element, or null if not found.
     * @throws SeleniumException if an error occurred
     */
    public function tryFindElement($cssSelector)
    {
        $results = $this->findAllElements($cssSelector);
        return (isset($results[0])) ? $results[0] : null;
    }
    
    /**
     * Finds a set of elements by a CSS selector.
     * 
     * @param string $cssSelector A CSS selector.
     * @return array<WebElement> The (possibly) empty set of matched elements.
     */
    public function findAllElements($cssSelector)
    {
        $response = $this->sessionPost('/elements', array('using' => 'css selector', 'value' => $cssSelector));
        $result = array();
        foreach ($response as $responseElement) {
            $result[] = $this->createWebElement($responseElement['ELEMENT']);
        }
        return $result;
    }
    
    protected function createWebElement($elementId)
    {
        return new WebElement($this->server, $this->sessionPath, $elementId);
    }
    
    /**
     * Refreshes the current page.
     */
    public function refresh()
    {
        $this->sessionPost('/refresh');
    }
    
    /**
     * Returns the alert/confirm/prompt text.
     * @throws SeleniumException if there is no alert/confirm/prompt window.
     */
    public function getAlertText()
    {
        return $this->sessionGet('/alert_text');
    }
    
    /**
     * Accepts an alert/confirm/prompt by pressing OK/Yes.
     * @throws SeleniumException if there is no alert/confirm/prompt window.
     */
    public function acceptAlert()
    {
        $this->sessionPost('/accept_alert');
    }
    
    /**
     * Dismisses an alert/confirm/prompt by pressing Cancel/No/[X].
     * @throws SeleniumException if there is no alert/confirm/prompt window.
     */
    public function dismissAlert()
    {
        $this->sessionPost('/dismiss_alert');
    }
    
    /**
     * Writes to a JS prompt() and clicks 'Ok'.
     * @throws SeleniumException if there is no prompt() to answer.
     */
    public function answerPrompt($answer)
    {
        $this->sessionPost('/alert_text', array('text' => $answer));
        $this->sessionPost('/accept_alert');
    }
    
    /**
     * Runs some JavaScript on the current page.
     * 
     * @param string $script JavaScript code.
     * @param array $args Arguments to be made available in the script via the 'arguments' array. May contain WebElement objects.
     * @return The return value of the script, if any. If it contains DOM objects, they are returned as WebElement objects.
     */
    public function runJavascript($script, array $args = array())
    {
        $args = $this->webElementsToRequest($args);
        $response = $this->sessionPost('/execute', array('script' => $script, 'args' => $args));
        return $this->webElementsFromResponse($response);
    }
    
    protected function webElementsToRequest(array $a)
    {
        foreach ($a as $key => $value) {
            if (is_array($value)) {
                $a[$key] = $this->webElementsToRequest($value);
            } elseif ($value instanceof WebElement) {
                $a[$key] = array('ELEMENT' => $value->getSeleniumId());
            }
        }
        return $a;
    }
    
    protected function webElementsFromResponse($a)
    {
        if (is_array($a)) {
            if (isset($a['ELEMENT'])) {
                return $this->createWebElement($a['ELEMENT']);
            } else {
                foreach ($a as $key => $value) {
                    $a[$key] = $this->webElementsFromResponse($value);
                }
            }
        }
        return $a;
    }
    
    
    protected function filterUrl($url)
    {
        if ($this->baseUrl && strpos($url, '://') == false) {
            if ($url[0] == '/') {
                return $this->baseUrl . $url;
            } else {
                return $this->baseUrl . '/' . $url;
            }
            return $this->baseUrl . $url;
        } else {
            return $url;
        }
    }
    
    protected function openSession()
    {
        $resp = $this->server->post('/session', array(
            'desiredCapabilities' => $this->options
        ));
        $this->sessionPath = rtrim($this->server->getLastRedirectLocation(), '/');
        try {
            //$this->server->get($this->sessionPath); // ????
        } catch (Exception $e) {
            echo "WARNING: " . $e->getMessage();
        }
    }
    
    /**
     * You should call this when you're finished with the WebDriver
     * or else a browser window is left open for a long time for no reason.
     */
    public function closeSession()
    {
        $this->server->delete($this->sessionPath);
        $this->server = null;
    }
    
    protected function sessionGet($path)
    {
        return $this->server->get($this->sessionPath . $path);
    }
    
    protected function sessionPost($path, $params = null)
    {
        return $this->server->post($this->sessionPath . $path, $params);
    }
    
    protected function sessionDelete($path)
    {
        return $this->server->delete($this->sessionPath . $path);
    }
    
    protected function getDefaultOptions()
    {
        return array(
            'browserName' => 'firefox',
            'version' => '',
            'javascriptEnabled' => true,
            'nativeEvents' => false
        );
    }
}