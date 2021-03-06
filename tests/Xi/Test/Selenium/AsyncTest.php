<?php
namespace Xi\Test\Selenium;

/**
 * Tests for operations having to do with asynchronous javascript.
 */
class AsyncTest extends LibraryTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->browser->setImplicitWait(0.5);
        $this->browser->visit($this->testFileBaseUrl . '/async.html');
    }

    /**
     * @test
     */
    public function canWaitForAnElementMatchedByCssToAppearAsynchronously()
    {
        $button = $this->browser->find('#simbutton');

        $button->click();

        $result = $this->browser->waitForElement("#newdiv", 7);
        $this->assertEquals('div', $result->getTagName());
        $this->assertEquals('this element was "downloaded"', $result->getText());
    }

    /**
     * @test
     */
    public function throwsAnExceptionIfTheMatchingElementDoesntAppear()
    {
        $this->setExpectedException('Xi\Test\Selenium\SeleniumException');
        $this->browser->waitForElement('.asdasd', 7);
    }

    /**
     * @test
     */
    public function throwsAnExceptionIfTheMatchingElementDoesntAppearInTime()
    {
        $this->setExpectedException('Xi\Test\Selenium\SeleniumException');
        $this->browser->waitForElement('#newdiv', 2);
    }

    /**
     * @test
     */
    public function canWaitForPartialTextToAppearAsynchronously()
    {
        $button = $this->browser->find('#simbutton');
        $resultBefore = $this->browser->find('#result');

        $this->assertEmpty($resultBefore->getText());
        $button->click();

        $resultAfter = $this->browser->waitForText('don', 7); // Partial text should suffice
        $this->assertEquals('done', $resultAfter->getText());
        $this->assertEquals('done', $resultBefore->getText());
        $this->assertEquals('done', $this->browser->find('#result')->getText());
    }

    /**
     * @test
     */
    public function throwsAnExceptionIfThePartialTextDoesntAppear()
    {
        $this->setExpectedException('Xi\Test\Selenium\SeleniumException');
        $this->browser->waitForText('asdasd', 1);
    }
}