<?php
namespace Xi\Test\Selenium;

class ElementFinderTest extends LibraryTestCase
{
    // Would use PHPUnit's data provider thingy but it runs too early,
    // before the test starts and before we get $this->browser.
    protected function foreachContainer($func)
    {
        $func($this, $this->browser);
        $func($this, $this->browser->find('body'));
    }

    /**
     * @test
     */
    public function canFindSingleElementsOnThePageByCssSelector()
    {
        $this->foreachContainer(function($self, $container) {
            $element = $container->find('body ul > li');
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
                $container->find('body > .this-doesnt-exist');
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
            $element1 = $container->tryFind('p');
            $element2 = $container->tryFind('.this-doesnt-exist');
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
            $element = $container->findByText('ipsum');
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
                $container->findByText('asdasdasd');
            } catch (SeleniumException $e) {
            }
            $self->assertNotNull($e);
            $self->assertEquals(SeleniumException::NoSuchElement, $e->getCode());
        });
    }

    /**
     * @test
     */
    public function doesNotFindAnElementByTextIfTheTextIsInterruptedByTags()
    {
        $this->browser->findByText('dolor');
        $this->setExpectedException('Xi\Test\Selenium\SeleniumException');
        $this->browser->findByText('dolor sit');
    }

    /**
     * @test
     */
    public function canFindASetOfElementsOnThePageByCssSelectors()
    {
        $this->foreachContainer(function($self, $container) {
            $elements = $container->findAll('body ul > li');
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
            $elements = $container->findAll('body > .this-doesnt-exist');
            $self->assertEmpty($elements);
        });
    }

    /**
     * @test
     */
    public function canFindElementsByTheirPartialLabelTexts()
    {
        $this->foreachContainer(function($self, $container) {
            $element = $container->findByLabel('Disabled fie'); // (partial text should suffice)
            $self->assertNotNull($element);
            $self->assertNotNull('input', $element->getTagName());
            $self->assertNotNull('disabled-field', $element->getId());
        });
    }

    /**
     * @test
     */
    public function canFindSingleElementsByXPath()
    {
        $this->foreachContainer(function($self, $container) {
            $result = $container->find('//ul/li[1]', 'xpath');
            $self->assertEquals('one', $result->getText());
        });
    }

    /**
     * @test
     */
    public function canFindMultipleElementsByXPath()
    {
        $this->foreachContainer(function($self, $container) {
            $result = $container->findAll('//ul/li', 'xpath');
            $self->assertEquals(3, count($result));
            $self->assertEquals('one', $result[0]->getText());
            $self->assertEquals('two', $result[1]->getText());
            $self->assertEquals('three', $result[2]->getText());
        });
    }

    /**
     * @test
     */
    public function anElementCannotFindItselfByCss()
    {
        $this->assertEmpty($this->browser->find('body')->findAll('body'));
    }

    /**
     * @test
     */
    public function anElementCanFindItselfByUsingFindByText()
    {
        $lipsum = 'Lorem ipsum...';
        $p = $this->browser->findByText($lipsum);
        $this->assertEquals('first-paragraph', $p->getId());
        $this->assertEquals('first-paragraph', $p->findByText($lipsum)->getId());
        $this->assertEquals('first-paragraph', $p->findByText($lipsum)->findByText($lipsum)->getId());
    }

    /**
     * @test
     */
    public function canFindTableRowsWithAGivenTextInATableWithAHeader()
    {
        $row = $this->browser->findTableRowWithText('t1r2c2');
        $texts = array_map(function ($r) { return $r->getText(); }, $row->findAll('td'));
        $this->assertEquals(array('t1r2c1', 't1r2c2'), $texts);
    }

    /**
     * @test
     */
    public function ignoresTHeadAndTFootByDefaultWhenFindingTableRows()
    {
        $this->assertEmpty($this->browser->findAllTableRowsWithText('title1'));
    }

    /**
     * @test
     */
    public function canBeMadeToNotIgnoreTHeadAndTFootByDefaultWhenFindingTableRows()
    {
        $this->assertNotEmpty($this->browser->findAllTableRowsWithText('title1', false));
    }

    /**
     * @test
     */
    public function canFindTableRowsWithAGivenTextInATableWithoutAHeader()
    {
        $row = $this->browser->findTableRowWithText('t2r2c2');
        $texts = array_map(function ($r) { return $r->getText(); }, $row->findAll('td'));
        $this->assertEquals(array('t2r2c1', 't2r2c2'), $texts);
    }

    /**
     * @test
     */
    public function findTableRowWithTextThrowsIfRowNotFound()
    {
        $this->setExpectedException(
            'Xi\Test\Selenium\SeleniumException',
            "Failed to find a table row with the given text"
        );
        $this->browser->findTableRowWithText('Lorem ipsum...');
    }

    /**
     * @test
     */
    public function tryFindTableRowWithTextReturnsNullIfRowNotFound()
    {
        $this->assertNull($this->browser->tryFindTableRowWithText('Lorem ipsum...'));
    }
}
