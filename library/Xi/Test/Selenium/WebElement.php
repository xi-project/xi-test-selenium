<?php
namespace Xi\Test\Selenium;

class WebElement
{
    protected $server;
    protected $elementId;
    protected $elementPath;
    
    public function __construct(SeleniumServer $server, $sessionPath, $id)
    {
        $this->server = $server;
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
     * Returns the text of the element.
     */
    public function getText()
    {
        return $this->elementGet('/text');
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