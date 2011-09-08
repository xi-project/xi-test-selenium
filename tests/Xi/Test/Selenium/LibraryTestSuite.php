<?php
namespace Xi\Test\Selenium;

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
