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
     * Finds a (sub)element pointed to by a label tag's for attribute.
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
    
    /**
     * Waits for text to appear in a (sub)element.
     * 
     * @param string $text The text to wait for.
     * @param int|float $timeout The number of seconds to wait at most.
     */
    public function waitForText($text, $timeout = null)
    {
        $timeout = $timeout ?: $this->getDefaultWaitTimeout();
        $startTime = microtime(true);
        do {
            $timePassed = microtime(true) - $startTime;
            $sleepTime = min(1.0, $timePassed / 10.0); // The longer it takes, the less we waste CPU, but never wait for over 1 sec
            usleep($sleepTime * 1000000);
            
            try {
                $element = $this->findByText($text);
                // If we get the element too early, it won't have its properties populated.
                // Probably a bug (seen 2011-09-12, firefox, linux)
                if (strpos($element->getText(), $text) !== false) {
                    return $element;
                }
            } catch (SeleniumException $e) {
                if (!$e->isDueToElementMissingOrInvisible()) {
                    throw $e;
                }
            }
        } while ($timePassed <= $timeout);
        throw new SeleniumException("Element with text '$text' failed to appear in $timeout sec");
    }
    
    protected abstract function makeRelativePostRequest($relPath, $params);
    
    /**
     * Returns the number of seconds that `waitFor*` methods wait for.
     * @return int|float
     */
    public function getDefaultWaitTimeout()
    {
        return 5;
    }
}