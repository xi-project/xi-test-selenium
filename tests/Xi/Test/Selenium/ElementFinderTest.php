<?php
namespace Xi\Test\Selenium;

class ElementFinderTest extends LibraryTestCase
{
    // Would use PHPUnit's data provider thingy but it runs too early,
    // before the test starts and before we get $this->browser.
    protected function foreachContainer($func)
    {
        $func($this, $this->browser);
        $func($this, $this->browser->findElement('body'));
    }
    
    /**
     * @test
     */
    public function canFindSingleElementsOnThePageByCssSelector()
    {
        $this->foreachContainer(function($self, $container) {
            $element = $container->findElement('body ul > li');
            $self->assertInstanceOf('\Xi\Test\Selenium\WebElement', $element);
            $self->assertEquals('one', $element->getText());
        });
    }
    
    /**
     * @test
     */
    public function throwsAnExceptionIfItCannotFindAnElementByCssSelector()
    {
        $this->foreachContainer(function($self, $container) {
            try {
                $container->findElement('body > .this-doesnt-exist');
            } catch (SeleniumException $e) {
            }
            $self->assertNotNull($e);
            $self->assertEquals(SeleniumException::NoSuchElement, $e->getCode());
        });
    }
    
    /**
     * @test
     */
    public function canAlternativelyFindSingleElementsWithoutThrowing()
    {
        $this->foreachContainer(function($self, $container) {
            $element1 = $container->tryFindElement('p');
            $element2 = $container->tryFindElement('.this-doesnt-exist');
            $self->assertNotNull($element1);
            $self->assertNull($element2);
        });
    }
    
    /**
     * @test
     */
    public function canFindSingleElementsOnThePageByPartialText()
    {
        $this->foreachContainer(function($self, $container) {
            $element = $container->findElementWithText('ipsum');
            $self->assertInstanceOf('\Xi\Test\Selenium\WebElement', $element);
            $self->assertEquals('p', $element->getTagName());
            $self->assertEquals('Lorem ipsum...', $element->getText());
        });
    }
    
    /**
     * @test
     */
    public function throwsAnExceptionIfItCannotFindAnElementByPartialText()
    {
        $this->foreachContainer(function($self, $container) {
            try {
                $container->findElementWithText('asdasdasd');
            } catch (SeleniumException $e) {
            }
            $self->assertNotNull($e);
            $self->assertEquals(SeleniumException::NoSuchElement, $e->getCode());
        });
    }
    
    /**
     * @test
     */
    public function canFindASetOfElementsOnThePageByCssSelectors()
    {
        $this->foreachContainer(function($self, $container) {
            $elements = $container->findAllElements('body ul > li');
            $expectedTexts = array('one', 'two', 'three');
            $i = 0;
            foreach ($elements as $element) {
                $self->assertInstanceOf('\Xi\Test\Selenium\WebElement', $element);
                $self->assertEquals($expectedTexts[$i++], $element->getText());
            }
        });
    }
    
    /**
     * @test
     */
    public function canEndUpFindingAnEmptySetOfElements()
    {
        $this->foreachContainer(function($self, $container) {
            $elements = $container->findAllElements('body > .this-doesnt-exist');
            $self->assertEmpty($elements);
        });
    }
}