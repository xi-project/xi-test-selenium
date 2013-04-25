<?php
namespace Xi\Test\Selenium;

class BaseUrlTest extends LibraryTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->browser->setBaseUrl($this->testFileBaseUrl);
    }

    protected function tearDown()
    {
        if ($this->browser) {
            $this->browser->setBaseUrl(null);
        }
        parent::tearDown();
    }

    /**
     * @test
     */
    public function itPrependsBaseUrlToRelativeUrls()
    {
        $this->browser->visit('index.php');
        $this->assertEquals($this->getTestFileUrl('index.php'), $this->browser->getUrl());

        $this->browser->visit('/index.php');
        $this->assertEquals($this->getTestFileUrl('index.php'), $this->browser->getUrl());
    }

    /**
     * @test
     */
    public function itIgnoresBaseUrlForAbsoluteUrls()
    {
        $url = 'file://' . dirname($this->testFileBasePath);
        $this->browser->visit($url);
        $this->assertEquals($url, $this->browser->getUrl());
    }

    /**
     * @test
     */
    public function canVisitAboutBlankWithBaseUrlSet()
    {
        $this->browser->visit('about:blank');
        $this->assertEquals('about:blank', $this->browser->getUrl());
    }
}
