<?php
namespace Xi\Test\Selenium;

/**
 * Constants for special key codes for use with WebElement::fillIn().
 *
 * See http://code.google.com/p/selenium/wiki/JsonWireProtocol#/session/:sessionId/element/:id/value
 *
 * See also e.g. http://www.ltg.ed.ac.uk/~richard/utf-8.html for the UTF-8 conversion
 */
class Keys
{
    const RELEASE_MODIFIERS = "\xEE\x80\x80";
    const BACKSPACE = "\xEE\x80\x83";
    const TAB = "\xEE\x80\x84";
    const RETURN_ = "\xEE\x80\x86";
    const ENTER = "\xEE\x80\x87";
    const SHIFT = "\xEE\x80\x88";
    const CONTROL = "\xEE\x80\x89";
    const ALT = "\xEE\x80\x8A";
    const ESCAPE = "\xEE\x80\x8C";
    const SPACE = "\xEE\x80\x8D";
    const DELETE = "\xEE\x80\x97";
    const COMMAND = "\xEE\x80\xBD";
    const META = "\xEE\x80\xBD";
}
