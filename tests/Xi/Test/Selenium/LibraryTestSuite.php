<?php
namespace Xi\Test\Selenium;

/**
 * A test suite for xi-test-selenium and an example of its integration with PHPUnit.
 *
 * This setup uses WebDriverInjectingTestDecorator to sinject a single WebDriver instance
 * to all tests in the suite. The tests here inherit LibraryTestCase, which implements
 * WebDriverTest and reset the browser in their tearDown(). This is considerably faster than
 * starting a new Selenium session between each test case.
 */
class LibraryTestSuite
{
    public static function suite()
    {
        $suite = new \PHPUnit_Framework_TestSuite;
        
        $testFiles = glob(__DIR__ . '/*Test.php');
        sort($testFiles);
        
        foreach ($testFiles as $testFile) {
            $suite->addTestFile($testFile);
        }
        
        $server = new SeleniumServer('http://localhost:4444/wd/hub');
        return new PHPUnit\WebDriverInjectingTestDecorator($suite, $server);
    }
}
