<?php
namespace Xi\Test\Selenium;

/**
 * Provides finders of web elements for WebDriver and WebElement.
 */
abstract class HasWebElements // Would rather make this a trait
{
    /**
     * Finds a (sub)element by a CSS selector or XPath expression.
     *
     * @param string $matcher The matcher, format depending on $format.
     * @param string $format Either 'css' or 'xpath'. Default: 'css'.
     * @return WebElement The matched element. Never null.
     * @throws SeleniumException if an error occurred or no element matched
     */
    public function find($matcher, $format = 'css')
    {
        $using = $this->seleniumFormat($format);
        $response = $this->makeRelativePostRequest('/element', array('using' => $using, 'value' => $matcher));
        return $this->createWebElement($response['ELEMENT']);
    }

    /**
     * Tries to find a (sub)element by a CSS selector or XPath expression.
     *
     * @param string $matcher The matcher, format depending on $format.
     * @param string $format Either 'css' or 'xpath'. Default: 'css'.
     * @return WebElement The matched element, or null if not found.
     * @throws SeleniumException if an error occurred
     */
    public function tryFind($matcher, $format = 'css')
    {
        $results = $this->findAll($matcher, $format);
        return (isset($results[0])) ? $results[0] : null;
    }

    /**
     * Finds a set of (sub)elements by a CSS selector or XPath expression.
     *
     * @param string $matcher The matcher, format depending on $format.
     * @param string $format Either 'css' or 'xpath'. Default: 'css'.
     * @return array<WebElement> The (possibly empty) set of matched elements.
     */
    public function findAll($matcher, $format = 'css')
    {
        $using = $this->seleniumFormat($format);
        $response = $this->makeRelativePostRequest('/elements', array('using' => $using, 'value' => $matcher));
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
     * Waits for a (sub)element appear.
     *
     * @param string $matcher The matcher, format depending on $format.
     * @param string $format Either 'css' or 'xpath'. Default: 'css'.
     * @param int|float $timeout The number of seconds to wait at most. Has a default value.
     * @return WebElement The matched element. Never null.
     * @throws SeleniumException if an error occurred or no element matched
     */
    public function waitForElement($matcher, $format = 'css', $timeout = null)
    {
        $self = $this;
        return $this->pollForResult(function() use ($self, $matcher, $format) {
            return $self->tryFind($matcher, $format);
        }, $timeout, "No element matching $format `$matcher` appeared in $timeout sec");
    }

    /**
     * Waits for text to appear in a (sub)element.
     *
     * This is the default way to check that text appears on a page.
     *
     * @param string $text The text to wait for.
     * @param int|float $timeout The number of seconds to wait at most. Has a default value.
     * @return WebElement The element that contained the text. Never null.
     * @throws SeleniumException if an error occurred or the timeout elapsed.
     */
    public function waitForText($text, $timeout = null)
    {
        $self = $this;
        return $this->pollForResult(function() use ($self, $text) {
            try {
                return $self->findByText($text);
            } catch (SeleniumException $e) {
                if (!$e->isDueToElementMissingOrInvisible()) {
                    throw $e;
                }
            }
            return null;
        }, $timeout, "Element with text '$text' failed to appear in $timeout sec");
    }

    /**
     * Calls `$func` repeatedly until it returns a non-null value or `$timeout` seconds have elapsed.
     *
     * Throws a SeleniumException on timeout. Exceptions from $func pass through.
     *
     * This is used internally to implement the `waitFor*` methods,
     * but can also be used by client code.
     *
     * @param callable $func A callback to invoke.
     * @param int|float $timeout The number of seconds to try for.
     * @param string $timeoutMsg The message of the SeleniumException to throw on timeout.
     * @return mixed The return value of `$func`
     * @throws SeleniumException if `$func` only returns nulls and the timeout elapses.
     * @throws Exception if `$func` throws it.
     */
    public function pollForResult($func, $timeout = null, $timeoutMsg = 'timeout')
    {
        $timeout = $timeout ?: $this->getDefaultWaitTimeout();
        $startTime = microtime(true);
        do {
            $timePassed = microtime(true) - $startTime;
            $sleepTime = min(1.0, $timePassed / 10.0); // The longer it takes, the less we waste CPU, but never wait for over 1 sec
            usleep($sleepTime * 1000000);

            $result = call_user_func($func);
            if ($result !== null) {
                return $result;
            }
        } while ($timePassed <= $timeout);
        throw new SeleniumException($timeoutMsg, SeleniumException::Timeout);
    }

    private function seleniumFormat($format)
    {
        switch ($format) {
            case 'css': return 'css selector';
            case 'xpath': return 'xpath';
            default: return $format; // Selenium will throw an exception if it's wrong.
        }
    }

    protected abstract function makeRelativePostRequest($relPath, $params);

    protected abstract function createWebElement($elementId);

    /**
     * Returns the number of seconds that `waitFor*` methods wait for.
     * @return int|float
     */
    public function getDefaultWaitTimeout()
    {
        return 5;
    }
}
