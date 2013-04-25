<?php
namespace Xi\Test\Selenium;
use Xi\Test\Selenium\PHPUnit\WebDriverTestCase;

abstract class LibraryTestCase extends WebDriverTestCase
{
    /**
     * @var string
     */
    protected $testFileBasePath;

    /**
     * @var string
     */
    protected $testFileBaseUrl;

    protected function setUp()
    {
        parent::setUp();

        $this->testFileBasePath =  PROJECT_ROOT . '/tests/testpages';
        $this->testFileBaseUrl = 'file://' . $this->testFileBasePath;

        $this->browser->visit($this->getTestFileUrl('index.html'));
    }

    protected function getTestFileUrl($name)
    {
        return $this->testFileBaseUrl . '/' . $name;
    }
}
