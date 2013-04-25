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

    protected function createWebElement($elementId)
    {
        return new static($this->server, $this->sessionPath, $elementId);
    }

    protected function makeRelativePostRequest($relPath, $params)
    {
        return $this->elementPost($relPath, $params);
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
