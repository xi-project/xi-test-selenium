<?php
namespace Xi\Test\Selenium;

/**
 * Provides finders of web elements for WebDriver and WebElement.
 */
abstract class HasWebElements // Would rather make this a trait
{
    /**
     * Finds a subelement by a CSS selector or XPath expression.
     *
     * @param string $matcher The matcher, format depending on $format.
     * @param string $format Either 'css' or 'xpath'. Default: 'css'.
     * @return WebElement The matched element. Never null.
     * @throws SeleniumException if an error occurred or no element matched
     */
    public function find($matcher, $format = 'css')
    {
        return $this->findImpl('one', $matcher, $format);
    }

    /**
     * Finds a subelement by a CSS selector or XPath expression, returns null if not found.
     *
     * @param string $matcher The matcher, format depending on $format.
     * @param string $format Either 'css' or 'xpath'. Default: 'css'.
     * @return WebElement The matched element, or null if not found.
     * @throws SeleniumException if an error occurred
     */
    public function tryFind($matcher, $format = 'css')
    {
        return $this->findImpl('tryOne', $matcher, $format);
    }

    /**
     * Finds a set of subelements by a CSS selector or XPath expression.
     *
     * @param string $matcher The matcher, format depending on $format.
     * @param string $format Either 'css' or 'xpath'. Default: 'css'.
     * @return WebElement[] The (possibly empty) set of matched elements.
     */
    public function findAll($matcher, $format = 'css')
    {
        return $this->findImpl('all', $matcher, $format);
    }

    /**
     * Finds a (sub)element that immediately contains the given text.
     *
     * Note that, unlike the selector-based find methods, this method may return the current element.
     *
     * It only finds text that is not interrupted by tags. This limitation may be removed in a future version.
     *
     * @param string $text The text to search for.
     * @return WebElement The element that contained the text as a substring.
     * @throws SeleniumException when the text cannot be found
     */
    public function findByText($text)
    {
        return $this->findByTextImpl('one', $text);
    }

    /**
     * Finds all subelements that immediately contain the given text.
     *
     * Note that, unlike the selector-based find methods, this method may return the current element.
     *
     * It only finds text that is not interrupted by tags. This limitation may be removed in a future version.
     *
     * @param string $text The text to search for.
     * @return WebElement[] The elements that contained the text as a substring.
     */
    public function findAllByText($text)
    {
        return $this->findByTextImpl('all', $text);
    }

    /**
     * Finds a (sub)element that immediately contains the given text, returns null if not found.
     *
     * Note that, unlike the selector-based find methods, this method may return the current element.
     *
     * It only finds text that is not interrupted by tags. This limitation may be removed in a future version.
     *
     * @param string $text The text to search for.
     * @return WebElement|null The element that contained the text as a substring, or null if not found.
     */
    public function tryFindByText($text)
    {
        return $this->findByTextImpl('tryOne', $text);
    }

    protected function findByTextImpl($mode, $text)
    {
        $expr = '//*[contains(text(),\'' . addslashes($text) . '\')]';
        return $this->findImpl($mode, $expr, 'xpath');
    }

    /**
     * Finds a subelement pointed to by a label tag's for attribute.
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
     * Given an array, finds elements whose labels correspond to the keys and fills in the values.
     *
     * This makes the common case of filling in large forms more convenient.
     *
     * @param array $labelsToValues An array where keys are valid for findByLabel and values are valid for fillIn().
     * @throws SeleniumException if some label cannot be found or it does not point to a valid element
     */
    public function fillInByLabels(array $labelsToValues)
    {
        foreach ($labelsToValues as $key => $value) {
            try {
                $this->findByLabel($key)->fillIn($value);
            } catch (SeleniumException $e) {
                throw new SeleniumException("Failed to fill in '$key' with '$value'", $e->getCode(), $e);
            }
        }
    }

    /**
     * Waits for a subelement appear for an extended period of time.
     *
     * By default, all elements are waited for for a short while. See WebDriver::setImplicitWait().
     *
     * @param string $matcher The matcher, format depending on $format.
     * @param int|float $timeout The number of seconds to wait at most.
     * @param string $format Either 'css' or 'xpath'. Default: 'css'.
     * @return WebElement The matched element. Never null.
     * @throws SeleniumException if an error occurred or no element matched
     */
    public function waitForElement($matcher, $timeout, $format = 'css')
    {
        $self = $this;
        return $this->withImplicitWait(function() use ($self, $matcher, $format) {
            return $self->find($matcher, $format);
        }, $timeout, "No element matching $format `$matcher` appeared in $timeout sec");
    }

    /**
     * Waits for text to appear in a subelement.
     *
     * By default, all elements are waited for for a short while. See WebDriver::setImplicitWait().
     *
     * @param string $text The text to wait for.
     * @param int|float $timeout The number of seconds to wait at most. Has a default value.
     * @return WebElement The element that contained the text. Never null.
     * @throws SeleniumException if an error occurred or the timeout elapsed.
     */
    public function waitForText($text, $timeout)
    {
        $self = $this;
        return $this->withImplicitWait(function() use ($self, $text) {
            return $self->findByText($text);
        }, $timeout, "Element with text '$text' failed to appear in $timeout sec");
    }

    /**
     * Sets an implicit wait of $timeout, calls $func, then restores the previous implicit wait.
     *
     * Throws a SeleniumException on timeout. Exceptions from $func pass through.
     *
     * This is used internally to implement the `waitFor*` methods,
     * but can also be used by client code.
     *
     * By default, all elements are waited for for a short while. See WebDriver::setImplicitWait().
     *
     * @param callable $func A callback to invoke.
     * @param int|float $timeout The number of seconds to set the implicit wait to while $func executes.
     * @return mixed The return value of $func
     * @throws SeleniumException if the timeout elapses or $func throws it.
     * @throws \Exception if $func throws it.
     */
    public function withImplicitWait($func, $timeout = null)
    {
        $oldImplicitWait = $this->getSession()->getImplicitWait();
        $this->getSession()->setImplicitWait($timeout);
        try {
            $ret = call_user_func($func);
        } catch (\Exception $e) {
            $this->getSession()->setImplicitWait($oldImplicitWait);
            throw $e;
        }
        $this->getSession()->setImplicitWait($oldImplicitWait);
        return $ret;
    }

    protected function findImpl($mode, $expr, $format)
    {
        assert(in_array($mode, array('all', 'one', 'tryOne')));
        $fetchAll = ($mode === 'all' || $mode === 'tryOne');

        $url = $fetchAll ? '/elements' : '/element';
        $using = $this->seleniumFormat($format);
        $response = $this->makeRelativePostRequest($url, array('using' => $using, 'value' => $expr));

        if ($mode === 'all') {
            $result = array();
            foreach ($response as $responseElement) {
                $result[] = $this->createWebElement($responseElement['ELEMENT']);
            }
            return $result;
        } elseif ($mode === 'one') {
            return $this->createWebElement($response['ELEMENT']);
        } elseif ($mode === 'tryOne') {
            $first = array_shift($response);
            if ($first !== null) {
                return $this->createWebElement($first['ELEMENT']);
            } else {
                return null;
            }
        } else {
            throw new \Exception("Internal error: findImpl called with incorrect \$mode: $mode.");
        }
    }

    private function seleniumFormat($format)
    {
        switch ($format) {
            case 'css': return 'css selector';
            case 'xpath': return 'xpath';
            default: return $format; // Selenium will throw an exception if it's wrong.
        }
    }

    /**
     * @return WebDriver
     */
    protected abstract function getSession();

    protected abstract function makeRelativePostRequest($relPath, $params);

    protected abstract function createWebElement($elementId);
}
