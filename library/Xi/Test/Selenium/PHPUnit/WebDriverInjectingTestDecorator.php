<?php
namespace Xi\Test\Selenium\PHPUnit;
use Xi\Test\Selenium\SeleniumServer,
    Xi\Test\Selenium\WebDriver;

/**
 * Injects a WebDriver into a test suite (or individual test) and properly disposes of it afterwards.
 *
 * It calls setUpWebDriver() for any test implementing WebDriverTest.
 * This is done recursively if the test is an IteratorAggregate (e.g. a PHPUnit test suite).
 */
class WebDriverInjectingTestDecorator extends \PHPUnit_Extensions_TestDecorator
{
    /**
     * @var SeleniumServer
     */
    protected $server;

    public function __construct(\PHPUnit_Framework_Test $test, SeleniumServer $server)
    {
        parent::__construct($test);
        $this->server = $server;
    }

    public function basicRun(\PHPUnit_Framework_TestResult $result)
    {
        $webDriver = $this->createWebDriver();

        try {
            $this->injectWebDriver($webDriver, $this->test);
            $result = parent::basicRun($result);
        } catch (\Exception $e) {
            $webDriver->closeSession();
            throw $e;
        }

        $webDriver->closeSession();
        return $result;
    }

    protected function injectWebDriver(WebDriver $webDriver, $test)
    {
        if ($test instanceof WebDriverTest) {
            $test->setUpWebDriver($webDriver);
        }
        if ($test instanceof \IteratorAggregate) {
            foreach ($test as $subtest) {
                $this->injectWebDriver($webDriver, $subtest);
            }
        }
    }

    protected function createWebDriver()
    {
        return new WebDriver($this->server);
    }
}
