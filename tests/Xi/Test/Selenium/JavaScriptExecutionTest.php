<?php
namespace Xi\Test\Selenium;

class JavaScriptExecutionTest extends TestCase
{
    /**
     * @test
     */
    public function executedJavascriptCanReturnValues()
    {
        $result = $this->browser->runJavascript('return ["x", 1, true, false, null]');
        $this->assertEquals(array("x", 1, true, false, null), $result);
    }
    
    /**
     * @test
     */
    public function executedJavascriptCanTakeAListOfArguments()
    {
        $result = $this->browser->runJavascript('return 1 + arguments[0] + arguments[1]', array(2, 4));
        $this->assertEquals(7, $result);
    }
    
    /**
     * @test
     */
    public function executedJavascriptCanTakeWebElementsAsParameters()
    {
        $para = $this->browser->findElement('#first-paragraph');
        $this->browser->runJavascript('$(arguments[0]).text("new text")', array($para));
        $this->assertEquals("new text", $para->getText());
    }
    
    /**
     * @test
     */
    public function executedJavascriptCanReturnWebElements()
    {
        $result = $this->browser->runJavascript('return {yay: $("#first-paragraph").text("foo")[0]}');
        $this->assertTrue(isset($result['yay']));
        $this->assertInstanceOf('\Xi\Test\Selenium\WebElement', $result['yay']);
        $this->assertEquals("foo", $result['yay']->getText());
    }
}