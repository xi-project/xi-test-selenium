<?php
namespace Xi\Test\Selenium;

class WebElementTest extends TestCase
{
    /**
     * @var WebElement
     */
    private $body;
    
    public function setUp()
    {
        parent::setUp();
        
        $this->body = $this->browser->findElement('body');
    }
    
    /**
     * @test
     */
    public function canGetTheTextOfTheElement()
    {
        $this->assertEquals('Lorem ipsum...', $this->browser->findElement('p#first-paragraph')->getText());
    }
    
    /**
     * @test
     */
    public function canFindSingleSubelementsByCssSelectors()
    {
        $element = $this->body->findSubelement('ul > li');
        $this->assertInstanceOf('\Xi\Test\Selenium\WebElement', $element);
        $this->assertEquals('one', $element->getText());
    }
    
    /**
     * @test
     */
    public function throwsAnExceptionIfItCannotFindASubelement()
    {
        try {
            $this->body->findSubelement('.this-doesnt-exist');
        } catch (SeleniumException $e) {
        }
        $this->assertNotNull($e);
        $this->assertEquals(SeleniumException::NoSuchElement, $e->getCode());
    }
    
    /**
     * @test
     */
    public function canAlternativelyFindSingleSubelementsWithoutThrowing()
    {
        $element1 = $this->body->tryFindSubelement('p');
        $element2 = $this->body->tryFindSubelement('.this-doesnt-exist');
        $this->assertNotNull($element1);
        $this->assertNull($element2);
    }
    
    /**
     * @test
     */
    public function canFindASetOfSubelementsByCssSelectors()
    {
        $elements = $this->body->findAllSubelements('ul > li');
        $expectedTexts = array('one', 'two', 'three');
        $i = 0;
        foreach ($elements as $element) {
            $this->assertInstanceOf('\Xi\Test\Selenium\WebElement', $element);
            $this->assertEquals($expectedTexts[$i++], $element->getText());
        }
    }
    
    /**
     * @test
     */
    public function canEndUpFindingAnEmptySetOfSubelements()
    {
        $elements = $this->body->findAllSubelements('body > .this-doesnt-exist');
        $this->assertEmpty($elements);
    }
    
    /**
     * @test
     */
    public function canBeClickedOn()
    {
        $this->body->findSubelement('#the-button')->click();
        $this->assertEquals('The button was clicked', $this->body->findSubelement('#the-result')->getText());
    }
    
    /**
     * @test
     */
    public function canGetAttributes()
    {
        $this->assertEquals('first-paragraph', $this->body->findSubelement('p')->getAttribute('id'));
        $this->assertNull($this->body->getAttribute('foobar'));
    }
    
    /**
     * @test
     */
    public function canReceiveInput()
    {
        $field = $this->body->findSubelement('#the-field');
        $field->fillIn("hello there");
        $this->assertEquals('hello there', $field->getAttribute('value'));
    }
    
    /**
     * @test
     */
    public function canReceiveSpecialKeysAmongInput()
    {
        $field = $this->body->findSubelement('#the-field');
        $field->fillIn("hellox" . Keys::BACKSPACE . Keys::SPACE . "there");
        $this->assertEquals('hello there', $field->getAttribute('value'));
    }
    
}