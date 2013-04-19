<?php
namespace Xi\Test\Selenium\PHPUnit;
use Xi\Test\Selenium\WebDriver;

/**
 * A convenient base class for PHPUnit Selenium tests.
 *
 * It adds setUp that makes \c $this->browser be SeleniumSingleton::getWebDriver() and
 * adds a tearDown to go to about:blank and clear cookies.
 */
abstract class WebDriverTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var WebDriver
     */
    public $browser; // Public for use with PHP 5.3 closures

    protected function setUp()
    {
        parent::setUp();
        $this->browser = SeleniumSingleton::getWebDriver();
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->browser->visit('about:blank');
        $this->browser->clearCookies();
    }
}
