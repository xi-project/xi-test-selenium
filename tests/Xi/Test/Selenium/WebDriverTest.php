<?php
namespace Xi\Test\Selenium;

class WebDriverTest extends LibraryTestCase
{
    /**
     * @test
     */
    public function canGetTheBrowsersCurrentUrl()
    {
        $this->assertEquals($this->getTestFileUrl('index.html'), $this->browser->getUrl());
    }
    
    /**
     * @test
     */
    public function canGetThePageTitle()
    {
        $this->assertEquals('Test page for xi-test-selenium', $this->browser->getPageTitle());
    }
    
    /**
     * @test
     */
    public function canGetThePageSourceCode()
    {
        $source = $this->browser->getPageSourceCode();
        
        $this->assertTrue(strpos($source, '<title>') !== false);
        $this->assertTrue(strpos($source, '<body>') !== false);
        $this->assertTrue(strpos($source, '<!DOCTYPE html>') !== false);
    }
    
    /**
     * @test
     */
    public function canMoveBackwardsAndForwardsInTheHistory()
    {
        $this->browser->visit($this->getTestFileUrl('index.html'));
        $this->browser->visit($this->getTestFileUrl('another.html'));
        $this->browser->back();
        $this->assertEquals($this->getTestFileUrl('index.html'), $this->browser->getUrl());
        $this->browser->forward();
        $this->assertEquals($this->getTestFileUrl('another.html'), $this->browser->getUrl());
    }
    
    /**
     * @test
     */
    public function canExecuteJavaScriptOnThePage() // Tested further in JavaScriptExecutionTest
    {
        $this->browser->runJavascript('$("body").append(\'<div id="new-hello">hello</div>\')');
        $this->browser->find('div#new-hello');
    }
    
    /**
     * @test
     */
    public function canRefreshThePage()
    {
        $this->browser->runJavascript('$("body").append(\'<div id="new-hello">hello</div>\')');
        $this->browser->find('div#new-hello');
        $this->browser->refresh();
        $this->assertNull($this->browser->tryFind('div#new-hello'));
    }
}