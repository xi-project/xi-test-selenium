<?php
namespace Xi\Test\Selenium;

class WebElement
{
    protected $server;
    protected $sessionPath;
    protected $elementId;
    protected $elementPath;
    
    public function __construct(SeleniumServer $server, $sessionPath, $id)
    {
        $this->server = $server;
        $this->sessionPath = $sessionPath;
        $this->elementId = (string)$id;
        $this->elementPath = $sessionPath . '/element/' . $id;
    }
    
    /**
     * Returns the opaque ID Selenium has assigned to this element.
     * @return string
     */
    public function getSeleniumId()
    {
        return $this->elementId;
    }
    
    /*
     * Returns the name of the tag of the element.
     * @return string
     */
    public function getTagName()
    {
        return $this->elementGet('/name');
    }
    
    /**
     * Returns the text of the element.
     * @return string
     */
    public function getText()
    {
        return $this->elementGet('/text');
    }
    
    /**
     * Returns the value of an attribute of the element or null if there is no such attribute.
     * @return string
     */
    public function getAttribute($attributeName)
    {
        return $this->elementGet('/attribute/' . $attributeName);
    }
    
    /**
     * Returns whether this (form) element is enabled.
     */
    public function isEnabled()
    {
        return $this->elementGet('/enabled');
    }
    
    /**
     * Returns whether this (form) element is disabled.
     */
    public function isDisabled()
    {
        return !$this->isEnabled();
    }
    
    /**
     * Clicks on the element.
     */
    public function click()
    {
        $this->elementPost('/click');
    }
    
    /**
     * Types text to the element.
     * 
     * The text may contain special keyboard codes.
     * See the constants in the Keys class.
     */
    public function fillIn($text)
    {
        if (!is_array($text)) {
            $text = array((string)$text);
        }
        $this->elementPost('/value', array('value' => $text));
    }
    
    /**
     * Clears a text(area) input field of all input.
     */
    public function clear()
    {
        $this->elementPost('/clear');
    }
    
    /**
     * Tells whether this element (option, checkbox or radio button) is selected.
     * 
     * To select an element (even an option), `click()` on it.
     * 
     * @return boolean
     */
    public function isSelected()
    {
        return $this->elementGet('/selected');
    }
    
    /**
     * Finds a subelement of this element by a CSS selector.
     * 
     * @param string $cssSelector A CSS selector.
     * @return WebElement The matched element. Never null.
     * @throws SeleniumException if an error occurred or no element matched
     */
    public function findSubelement($cssSelector)
    {
        $response = $this->elementPost('/element', array('using' => 'css selector', 'value' => $cssSelector));
        return $this->createWebElement($response['ELEMENT']);
    }
    
    /**
     * Tries to find a subelement of this element by a CSS selector.
     * 
     * @param string $cssSelector A CSS selector.
     * @return WebElement The matched element, or null if not found.
     * @throws SeleniumException if an error occurred
     */
    public function tryFindSubelement($cssSelector)
    {
        $results = $this->findAllSubelements($cssSelector);
        return (isset($results[0])) ? $results[0] : null;
    }
    
    /**
     * Finds a set of subelements of this element by a CSS selector.
     * 
     * @param string $cssSelector A CSS selector.
     * @return array<WebElement> The (possibly) empty set of matched elements.
     */
    public function findAllSubelements($cssSelector)
    {
        $response = $this->elementPost('/elements', array('using' => 'css selector', 'value' => $cssSelector));
        $result = array();
        foreach ($response as $responseElement) {
            $result[] = $this->createWebElement($responseElement['ELEMENT']);
        }
        return $result;
    }
    
    protected function createWebElement($elementId)
    {
        return new static($this->server, $this->sessionPath, $elementId);
    }
    
    protected function elementGet($path)
    {
        return $this->server->get($this->elementPath . $path);
    }
    
    protected function elementPost($path, $params = null)
    {
        return $this->server->post($this->elementPath . $path, $params);
    }
    
    protected function elementDelete($path)
    {
        return $this->server->delete($this->elementPath . $path);
    }
}