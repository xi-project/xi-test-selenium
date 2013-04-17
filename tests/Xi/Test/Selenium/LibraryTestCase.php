<?php
namespace Xi\Test\Selenium;
use Xi\Test\Selenium\PHPUnit\WebDriverTestCase;

abstract class LibraryTestCase extends WebDriverTestCase
{
    /**
     * @var string
     */
    protected $testFileBaseUrl;

    public function setUp()
    {
        parent::setUp();

        $this->testFileBaseUrl = 'file://' . PROJECT_ROOT . '/tests/testpages';

        $this->browser->visit($this->getTestFileUrl('index.html'));
    }

    protected function getTestFileUrl($name)
    {
        return $this->testFileBaseUrl . '/' . $name;
    }
}
