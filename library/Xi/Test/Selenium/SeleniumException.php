<?php
namespace Xi\Test\Selenium;

class SeleniumException extends \Exception
{
    // Search & replace pattern to generate from the string in getErrorCodes()
    // Search: /^(\d+)\s+(\w+).*/
    // Replace: const \2 = \1;
    const NoSuchElement = 7;
    const NoSuchFrame = 8;
    const UnknownCommand = 9;
    const StaleElementReference = 10;
    const ElementNotVisible = 11;
    const InvalidElementState = 12;
    const UnknownError = 13;
    const ElementIsNotSelectable = 15;
    const JavaScriptError = 17;
    const XPathLookupError = 19;
    const Timeout = 21;
    const NoSuchWindow = 23;
    const InvalidCookieDomain = 24;
    const UnableToSetCookie = 25;
    const UnexpectedAlertOpen = 26;
    const NoAlertOpenError = 27;
    const ScriptTimeout = 28;
    const InvalidElementCoordinates = 29;
    const IMENotAvailable = 30;
    const IMEEngineActivationFailed = 31;
    const InvalidSelector = 32;
    
    private static $errorCodes;
    
    public function __construct($message = null, $code = 0, $previous = null)
    {
        $code = (int)$code;
        if (empty($message)) {
            $codes = self::getErrorCodes();
            if (isset($codes[$code])) {
                $message = $codes[$code]['summary'] . " ($code): " . $codes[$code]['description'];
            } else {
                $message = "Selenium exception ($code)";
            }
        }
        parent::__construct($message, $code, $previous);
    }
    
    /**
     * Returns whether the error code was NoSuchElement or ElementNotVisible
     * @return boolean
     */
    public function isDueToElementMissingOrInvisible()
    {
        return in_array($this->getCode(), array(self::NoSuchElement, self::ElementNotVisible));
    }
    
    private static function getErrorCodes()
    {
        if (!self::$errorCodes) {
            // Copy-pasted from the JsonWireProtocol wiki page
            // Code	Summary	Detail
            $data = <<<EOS
0	Success	 The command executed successfully.
7	NoSuchElement	 An element could not be located on the page using the given search parameters.
8	NoSuchFrame	 A request to switch to a frame could not be satisfied because the frame could not be found.
9	UnknownCommand	 The requested resource could not be found, or a request was received using an HTTP method that is not supported by the mapped resource.
10	StaleElementReference	 An element command failed because the referenced element is no longer attached to the DOM.
11	ElementNotVisible	 An element command could not be completed because the element is not visible on the page.
12	InvalidElementState	 An element command could not be completed because the element is in an invalid state (e.g. attempting to click a disabled element).
13	UnknownError	 An unknown server-side error occurred while processing the command.
15	ElementIsNotSelectable	 An attempt was made to select an element that cannot be selected.
17	JavaScriptError	 An error occurred while executing user supplied JavaScript.
19	XPathLookupError	 An error occurred while searching for an element by XPath.
21	Timeout	 An operation did not complete before its timeout expired.
23	NoSuchWindow	 A request to switch to a different window could not be satisfied because the window could not be found.
24	InvalidCookieDomain	 An illegal attempt was made to set a cookie under a different domain than the current page.
25	UnableToSetCookie	 A request to set a cookie's value could not be satisfied.
26	UnexpectedAlertOpen	 A modal dialog was open, blocking this operation
27	NoAlertOpenError	 An attempt was made to operate on a modal dialog when one was not open.
28	ScriptTimeout	 A script did not complete before its timeout expired.
29	InvalidElementCoordinates	 The coordinates provided to an interactions operation are invalid.
30	IMENotAvailable	 IME was not available.
31	IMEEngineActivationFailed	 An IME engine could not be started.
32	InvalidSelector	 Argument was an invalid selector (e.g. XPath/CSS).
EOS;
            self::$errorCodes = array();
            foreach (array_map('trim', explode("\n", $data)) as $line) {
                if (!empty($line)) {
                    list($code, $summary, $description) = preg_split('/\s+/', $line, 3);
                    $code = (int)$code;
                    self::$errorCodes[$code] = array(
                        'code' => $code,
                        'summary' => $summary,
                        'description' => $description
                    );
                }
            }
        }
        
        return self::$errorCodes;
    }
}