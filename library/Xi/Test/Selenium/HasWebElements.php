<?php
namespace Xi\Test\Selenium;

/**
 * Provides finders for web elements.
 */
abstract class HasWebElements // Would rather make this a trait
{
    /**
     * Finds a (sub)element by a CSS selector.
     * 
     * @param string $cssSelector A CSS selector.
     * @return WebElement The matched element. Never null.
     * @throws SeleniumException if an error occurred or no element matched
     */
    public function find($cssSelector)
    {
        $response = $this->makeRelativePostRequest('/element', array('using' => 'css selector', 'value' => $cssSelector));
        return $this->createWebElement($response['ELEMENT']);
    }
    
    /**
     * Tries to find a (sub)element by a CSS selector.
     * 
     * @param string $cssSelector A CSS selector.
     * @return WebElement The matched element, or null if not found.
     * @throws SeleniumException if an error occurred
     */
    public function tryFind($cssSelector)
    {
        $results = $this->findAll($cssSelector);
        return (isset($results[0])) ? $results[0] : null;
    }
    
    /**
     * Finds a set of (sub)elements by a CSS selector.
     * 
     * @param string $cssSelector A CSS selector.
     * @return array<WebElement> The (possibly empty) set of matched elements.
     */
    public function findAll($cssSelector)
    {
        $response = $this->makeRelativePostRequest('/elements', array('using' => 'css selector', 'value' => $cssSelector));
        $result = array();
        foreach ($response as $responseElement) {
            $result[] = $this->createWebElement($responseElement['ELEMENT']);
        }
        return $result;
    }
    
    /**
     * Finds a (sub)element that contains the given text.
     * 
     * @param string $text The text to search for.
     * @return WebElement The element that contained the text as a substring.
     * @throws SeleniumException when the text cannot be found
     */
    public function findByText($text)
    {
        $expr = '//*[contains(text(),\'' . addslashes($text) . '\')]';
        $response = $this->makeRelativePostRequest('/element', array('using' => 'xpath', 'value' => $expr));
        return $this->createWebElement($response['ELEMENT']);
    }
    
    /**
     * Finds an element pointed to by a label tag's for attribute.
     * 
     * @param string $labelText The text of the label whose element to search for.
     * @return WebElement The element the label with the given text points to with its `for` attribute.
     * @throws SeleniumException if the label cannot be found or it does not point to a valid element
     */
    public function findByLabel($labelText)
    {
        $element = $this->findByText($labelText);
        if ($element->getTagName() != 'label') {
            throw new SeleniumException("Tag with text '$labelText' was not a label but a " . $element->getTagName());
        }
        $for = $element->getAttribute('for');
        if (empty($for)) {
            throw new SeleniumException("Label with text '$labelText' has no for attribute");
        }
        return $this->find('#' . $for);
    }
    
    protected abstract function makeRelativePostRequest($relPath, $params);
}