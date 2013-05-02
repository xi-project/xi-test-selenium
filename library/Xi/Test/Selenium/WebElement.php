<?php
namespace Xi\Test\Selenium;

/**
 * An element in the HTML document.
 *
 * To use your own subclass, replace the protected createWebElement()
 * in a subclass of WebDriver.
 */
class WebElement extends HasWebElements
{
    protected $session;
    protected $sessionPath;
    protected $elementId;
    protected $elementPath;

    public function __construct(WebDriver $session, $sessionPath, $id)
    {
        $this->session = $session;
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

    /**
     * Returns the WebDriver instance that this element resides in.
     * @return WebDriver
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * Returns the HTML id of the element.
     * @return string
     */
    public function getId()
    {
        return $this->getAttribute('id');
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
     * @param $attributeName
     * @return string|null
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
     * Returns whether this element is hidden (e.g. by CSS `display: none` or similar).
     */
    public function isHidden()
    {
        return !$this->elementGet('/displayed');
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
    public function fillIn($text, $clearFirst = true)
    {
        if ($clearFirst) {
            $this->clear();
        }
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
     * Returns the parent element, if any.
     *
     * @return WebElement|null
     */
    public function getParent()
    {
        return $this->tryFind('..', 'xpath');
    }

    /**
     * Returns all ancestor elements of this element, deepest i.e. closest first.
     *
     * @param callable|null $predicate An optional filter predicate function.
     * @return WebElement[]
     */
    public function getAncestors($predicate = null)
    {
        $result = array();
        $p = $this;
        // We test against the tag name because get an error if we try to select
        // the document object (i.e. the thing above <html>).
        // This is a kludge that assumes an HTML document. A better xpath-based solution is no doubt possible.
        while ($p->getTagName() != 'html') {
            $p = $p->getParent();
            if (!$predicate || call_user_func($predicate, $p)) {
                $result[] = $p;
            }
        }
        return $result;
    }

    /**
     * Returns the tag names of all ancestor elements of this element, deepest i.e. closest first.
     *
     * @return string[]
     */
    public function getAncestorTags()
    {
        $result = array();
        foreach ($this->getAncestors() as $ancestor) {
            $result[] = $ancestor->getTagName();
        }
        return $result;
    }

    protected function makeRelativePostRequest($relPath, $params)
    {
        return $this->elementPost($relPath, $params);
    }

    protected function createWebElement($elementId)
    {
        return new static($this->session, $this->sessionPath, $elementId);
    }

    protected function elementGet($path)
    {
        return $this->session->getServer()->get($this->elementPath . $path);
    }

    protected function elementPost($path, $params = null)
    {
        return $this->session->getServer()->post($this->elementPath . $path, $params);
    }

    protected function elementDelete($path)
    {
        return $this->session->getServer()->delete($this->elementPath . $path);
    }
}
