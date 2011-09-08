<?php
namespace Xi\Test\Selenium;

class TestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SeleniumServer
     */
    private static $seleniumServer;
    
    /**
     * @var WebDriver
     */
    private static $persistentBrowser;
    
    /**
     * @var WebDriver
     */
    protected $browser;
    
    /**
     * @var string
     */
    protected $testFileBaseUrl;
    
    public static function setUpBeforeClass()
    {
        if (!self::$seleniumServer) {
            self::$seleniumServer = new SeleniumServer('http://localhost:4444/wd/hub');
        }
        if (!self::$persistentBrowser) {
            self::$persistentBrowser = new WebDriver(self::$seleniumServer);
        }
    }
    
    public function setUp()
    {
        $this->testFileBaseUrl = 'file://' . PROJECT_ROOT . '/tests/testpages';
        
        $this->browser = self::$persistentBrowser; // Creating new browsers is very slow
        $this->browser->visit($this->getTestFileUrl('index.html'));
    }
    
    public function getTestFileUrl($name)
    {
        return $this->testFileBaseUrl . '/' . $name;
    }
    
    public function tearDown()
    {
        //$this->browser->clearCookies(); // TODO
        
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
    
    public static function tearDownAfterClass()
    {
        if (self::$persistentBrowser) {
            self::$persistentBrowser->closeSession();
            self::$persistentBrowser = null;
        }
    }
}