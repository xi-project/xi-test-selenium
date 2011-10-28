<?php
namespace Xi\Test\Selenium\PHPUnit;
use Xi\Test\Selenium\WebDriver;

/**
 * Implement this to have WebDriverInjectingTestDecorator inject
 * a WebDriver before each test run.
 */
interface WebDriverTest
{
    /**
     * Receives a WebDriver before each test run.
     */
    public function setUpWebDriver(WebDriver $webDriver);
}

