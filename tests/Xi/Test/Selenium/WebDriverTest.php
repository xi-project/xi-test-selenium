<?php
namespace Xi\Test\Selenium;

class WebDriverTest extends TestCase
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
    public function canFindSingleElementsOnThePageByCssSelectors()
    {
        $element = $this->browser->findElement('body ul > li');
        $this->assertInstanceOf('\Xi\Test\Selenium\WebElement', $element);
        $this->assertEquals('one', $element->text());
    }
    
    /**
     * @test
     */
    public function throwsAnExceptionIfItCannotFindAnElement()
    {
        try {
            $this->browser->findElement('body > .this-doesnt-exist');
        } catch (SeleniumException $e) {
        }
        $this->assertNotNull($e);
        $this->assertEquals(SeleniumException::NoSuchElement, $e->getCode());
    }
    
    /**
     * @test
     */
    public function canAlternativelyFindSingleElementsWithoutThrowing()
    {
        $element1 = $this->browser->tryFindElement('body');
        $element2 = $this->browser->tryFindElement('body > .this-doesnt-exist');
        $this->assertNotNull($element1);
        $this->assertNull($element2);
    }
    
    /**
     * @test
     */
    public function canFindASetOfElementsOnThePageByCssSelectors()
    {
        $elements = $this->browser->findAllElements('body ul > li');
        $expectedTexts = array('one', 'two', 'three');
        $i = 0;
        foreach ($elements as $element) {
            $this->assertInstanceOf('\Xi\Test\Selenium\WebElement', $element);
            $this->assertEquals($expectedTexts[$i++], $element->text());
        }
    }
    
    /**
     * @test
     */
    public function canEndUpFindingAnEmptySetOfElementsOnThePageByCssSelectors()
    {
        $elements = $this->browser->findAllElements('body > .this-doesnt-exist');
        $this->assertEmpty($elements);
    }
    
    /**
     * @test
     */
    public function canExecuteJavaScriptOnThePage() // Tested further in JavaScriptExecutionTest
    {
        $this->browser->runJavascript('$("body").append(\'<div id="new-hello">hello</div>\')');
        $this->browser->findElement('div#new-hello');
    }
    
    /**
     * @test
     */
    public function canRefreshThePage()
    {
        $this->browser->runJavascript('$("body").append(\'<div id="new-hello">hello</div>\')');
        $this->browser->findElement('div#new-hello');
        $this->browser->refresh();
        $this->assertNull($this->browser->tryFindElement('div#new-hello'));
    }
}