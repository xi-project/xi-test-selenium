
# xi-test-selenium #

This is a PHP 5.3 wrapper around [Selenium 2](http://code.google.com/p/selenium/) WebDriver Server via the [wire protocol](http://code.google.com/p/selenium/wiki/JsonWireProtocol).

It is useful, reasonably well-tested, cleanly built and extensible. Although it does not wrap everything [yet](https://github.com/xi-project/xi-test-selenium/issues?labels=missing-binding), it can be used right now and trivially subclassed to meet any additional requirements (although a fork and a pull request would also be appreciated).

## Mini-Tutorial ##

Download the latest [selenium-server-standalone jar](http://code.google.com/p/selenium/downloads/list) and leave it running with `java -jar path/to/the.jar`.

Then integrate essentially the following things into your test setup.

```php
<?php
use \Xi\Test\Selenium\SeleniumServer,
    \Xi\Test\Selenium\WebDriver;

// An autoloader with the proper search paths is assumed

$server = new SeleniumServer('http://localhost:4444/wd/hub');
$browser = new WebDriver($server);
$browser->visit('/index');
$browser->findByLabel('Username')->fillIn('john');
$browser->findByLabel('Password')->fillIn('shepard');
$browser->find('form#login button[type=submit]')->click();
$browser->screenshot('after-login.png');
```

Read about all the cool methods available to you in the **[API documentation](http://xi-project.github.io/xi-test-selenium/)**.

See the `tests/` directory of this project for ideas on how to set this up with PHPUnit or similar.

## Competitors ##

The following libraries aim to do what we do.

* http://code.google.com/p/php-webdriver-bindings/
* https://github.com/chibimagic/WebDriver-PHP
