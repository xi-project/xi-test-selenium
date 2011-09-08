<?php
namespace Xi\Test\Selenium;

class AlertTest extends TestCase
{
    /**
     * @test
     */
    public function alertTextCanBeRetrieved()
    {
        $this->browser->runJavascript('alert("hello")');
        $this->assertEquals("hello", $this->browser->getAlertText());
        $this->browser->dismissAlert();
    }
    
    /**
     * @test
     */
    public function alertsCanBeAccepted()
    {
        $this->browser->runJavascript('$("#the-result").text(confirm("press ok kthx") ? "yes" : "no")');
        $this->browser->acceptAlert();
        $this->assertEquals('yes', $this->browser->findElement('#the-result')->getText());
    }
    
    /**
     * @test
     */
    public function alertsCanBeDismissed()
    {
        $this->browser->runJavascript('$("#the-result").text(confirm("press cancel kthx") ? "yes" : "no")');
        $this->browser->dismissAlert();
        $this->assertEquals('no', $this->browser->findElement('#the-result')->getText());
    }
    
    /**
     * @test
     */
    public function promptsCanBeAnswered()
    {
        $this->browser->runJavascript('$("#the-result").text(prompt("plz type stuff"))');
        $this->browser->answerPrompt('xoox');
        sleep(3);
        $this->assertEquals('xoox', $this->browser->findElement('#the-result')->getText());
    }
    
    /**
     * @test
     */
    public function tryingToReceiveAlertTextWhenThereIsNoAlertThrowsAnException()
    {
        $this->expectNoAlertOpenException();
        $this->browser->getAlertText();
    }
    
    private function expectNoAlertOpenException()
    {
        $this->setExpectedException('\Xi\Test\Selenium\SeleniumException', '', SeleniumException::NoAlertOpenError);
    }
}