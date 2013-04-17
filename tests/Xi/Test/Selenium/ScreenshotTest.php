<?php
namespace Xi\Test\Selenium;

class ScreenshotTest extends LibraryTestCase
{
    private $screenshotDir;

    public function setUp()
    {
        parent::setUp();
        $this->screenshotDir = PROJECT_ROOT . '/tests/screenshots';
    }

    /**
     * @test
     */
    public function itCanWriteScreenshotsAsPngFiles()
    {
        $path = $this->screenshotDir . '/screenie.png';
        @unlink($path);
        $this->browser->screenshot($path);
        $this->assertTrue(file_exists($path));
        $this->assertGreaterThan(100, filesize($path));
    }

    /**
     * @test
     */
    public function itThrowsAnExceptionWhenTryingToWriteAScreenshotToANonPngFile()
    {
        $this->setExpectedException('\Xi\Test\Selenium\SeleniumException');
        $this->browser->screenshot($this->screenshotDir . '/screenie.jpeg');
    }
}