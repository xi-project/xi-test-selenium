<?php
namespace Xi\Test\Selenium;

abstract class LibraryTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var WebDriver
     */
    public $browser; // public for use in PHP 5.3 closures
    
    /**
     * @var string
     */
    protected $testFileBaseUrl;
    
    public function setUpWebDriver(WebDriver $browser)
    {
        $this->browser = $browser;
    }
    
    public function setUp()
    {
        $this->testFileBaseUrl = 'file://' . PROJECT_ROOT . '/tests/testpages';
        
        $this->browser->visit($this->getTestFileUrl('index.html'));
    }
    
    public function getTestFileUrl($name)
    {
        return $this->testFileBaseUrl . '/' . $name;
    }
    
    public function tearDown()
    {
        $this->browser->clearCookies();
        
        // With the current (2.5.0) version of Selenium,
        // dismissAlert fails very slowly so we won't do the following :(
        // Hopefully this is fixed in later version of Selenium.
        /*
        try {
            $this->browser->dismissAlert();
        } catch (SeleniumException $e) {
            if ($e->getCode() != SeleniumException::NoAlertOpenError) {
                throw $e;
            }
        }
        */
        
        $this->browser->visit('about:blank');
    }
}