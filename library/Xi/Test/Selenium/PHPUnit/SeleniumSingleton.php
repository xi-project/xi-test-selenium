<?php
namespace Xi\Test\Selenium\PHPUnit;
use Xi\Test\Selenium\SeleniumServer;
use Xi\Test\Selenium\WebDriver;

/**
 * Shares a single SeleniumServer and WebDriver across all your tests, for efficiency.
 *
 * The default selenium server URL is "http://localhost:4444/wd/hub".
 * Set {@code <php><env name="SELENIUM_SERVER_URL" value="http://..." /></php>}
 * in your phpunit.xml to set the selenium server URL.
 */
class SeleniumSingleton
{
    const DEFAULT_SELENIUM_SERVER_URL = 'http://localhost:4444/wd/hub';

    protected static $server = null;
    protected static $webDriver = null;
    protected static $shutdownFunctionRegistered = false;

    /**
     * @return SeleniumServer
     */
    public static function getServer()
    {
        if (static::$server === null) {
            static::$server = new SeleniumServer(static::getConfiguredUrl());
        }
        return static::$server;
    }

    /**
     * Returns the singleton webdriver.
     *
     * Recreates it first if \c closeSession() has been called on the previous instance.
     *
     * @return WebDriver
     */
    public static function getWebDriver()
    {
        if (static::$webDriver === null || static::$webDriver->isClosed()) {
            static::$webDriver = new WebDriver(static::getServer());
            static::ensureShutdownFunctionRegistered();
        }
        return static::$webDriver;
    }

    /**
     * Disposes of the current instance of the web driver.
     *
     * Any future call to getWebDriver() will create a new one.
     */
    public static function closeWebDriver()
    {
        if (static::$webDriver !== null) {
            static::getWebDriver()->closeSession();
        }
    }

    public static function getConfiguredUrl()
    {
        if (isset($_ENV['SELENIUM_SERVER_URL'])) {
            return $_ENV['SELENIUM_SERVER_URL'];
        } else {
            return static::DEFAULT_SELENIUM_SERVER_URL;
        }
    }

    /**
     * This is automatically registered as a shutdown handler.
     */
    public static function shutDown()
    {
        SeleniumSingleton::closeWebDriver();
    }

    private static function ensureShutdownFunctionRegistered()
    {
        if (!static::$shutdownFunctionRegistered) {
            register_shutdown_function(array(get_called_class(), 'shutDown'));
            static::$shutdownFunctionRegistered = true;
        }
    }
}