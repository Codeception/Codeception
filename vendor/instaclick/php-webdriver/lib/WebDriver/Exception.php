<?php
/**
 * Copyright 2004-2012 Facebook. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @package WebDriver
 *
 * @author Justin Bishop <jubishop@gmail.com>
 * @author Anthon Pang <apang@softwaredevelopment.ca>
 */

namespace WebDriver;

/**
 * WebDriver\Exception class
 *
 * @package WebDriver
 */
abstract class Exception extends \Exception
{
    /**
     * Response status codes
     *
     * @link http://code.google.com/p/selenium/wiki/JsonWireProtocol#Response_Status_Codes
     */
    const SUCCESS = 0;
    const NO_SUCH_ELEMENT = 7;
    const NO_SUCH_FRAME = 8;
    const UNKNOWN_COMMAND = 9;
    const STALE_ELEMENT_REFERENCE = 10;
    const ELEMENT_NOT_VISIBLE = 11;
    const INVALID_ELEMENT_STATE = 12;
    const UNKNOWN_ERROR = 13;
    const ELEMENT_IS_NOT_SELECTABLE = 15;
    const JAVASCRIPT_ERROR = 17;
    const XPATH_LOOKUP_ERROR = 19;
    const TIMEOUT = 21;
    const NO_SUCH_WINDOW = 23;
    const INVALID_COOKIE_DOMAIN = 24;
    const UNABLE_TO_SET_COOKIE = 25;
    const UNEXPECTED_ALERT_OPEN = 26;
    const NO_ALERT_OPEN_ERROR = 27;
    const SCRIPT_TIMEOUT = 28;
    const INVALID_ELEMENT_COORDINATES = 29;
    const IME_NOT_AVAILABLE = 30;
    const IME_ENGINE_ACTIVATION_FAILED = 31;
    const INVALID_SELECTOR = 32;
    const SESSION_NOT_CREATED = 33;
    const MOVE_TARGET_OUT_OF_BOUNDS = 34;

    // obsolete
    const INDEX_OUT_OF_BOUNDS = 1;
    const NO_COLLECTION = 2;
    const NO_STRING = 3;
    const NO_STRING_LENGTH = 4;
    const NO_STRING_WRAPPER = 5;
    const NO_SUCH_DRIVER = 6;
    const OBSOLETE_ELEMENT = 10;
    const ELEMENT_NOT_DISPLAYED = 11;
    const UNHANDLED = 13;
    const EXPECTED = 14;
    const ELEMENT_NOT_SELECTABLE = 15;
    const NO_SUCH_DOCUMENT = 16;
    const UNEXPECTED_JAVASCRIPT = 17;
    const NO_SCRIPT_RESULT = 18;
    const NO_SUCH_COLLECTION = 20;
    const NULL_POINTER = 22;
    const NO_MODAL_DIALOG_OPEN_ERROR = 27;

    // user-defined
    const CURL_EXEC = -1;
    const OBSOLETE_COMMAND = -2;
    const NO_PARAMETERS_EXPECTED = -3;
    const JSON_PARAMETERS_EXPECTED = -4;
    const UNEXPECTED_PARAMETERS = -5;
    const INVALID_REQUEST = -6;
    const UNKNOWN_LOCATOR_STRATEGY = -7;
    const WEBTEST_ASSERTION = -8;

    private static $errs = array(
//      self::SUCCESS => array('Success', 'This should never be thrown!'),

        self::NO_SUCH_ELEMENT => array('NoSuchElement', 'An element could not be located on the page using the given search parameters.'),
        self::NO_SUCH_FRAME => array('NoSuchFrame', 'A request to switch to a frame could not be satisfied because the frame could not be found.'),
        self::UNKNOWN_COMMAND => array('UnknownCommand', 'The requested resource could not be found, or a request was received using an HTTP method that is not supported by the mapped resource.'),
        self::STALE_ELEMENT_REFERENCE => array('StaleElementReference', 'An element command failed because the referenced element is no longer attached to the DOM.'),
        self::ELEMENT_NOT_VISIBLE => array('ElementNotVisible', 'An element command could not be completed because the element is not visible on the page.'),
        self::INVALID_ELEMENT_STATE => array('InvalidElementState', 'An element command could not be completed because the element is in an invalid state (e.g., attempting to click a disabled element).'),
        self::UNKNOWN_ERROR => array('UnknownError', 'An unknown server-side error occurred while processing the command.'),
        self::ELEMENT_IS_NOT_SELECTABLE => array('ElementIsNotSelectable', 'An attempt was made to select an element that cannot be selected.'),
        self::JAVASCRIPT_ERROR => array('JavaScriptError', 'An error occurred while executing user supplied JavaScript.'),
        self::XPATH_LOOKUP_ERROR => array('XPathLookupError', 'An error occurred while searching for an element by XPath.'),
        self::TIMEOUT => array('Timeout', 'An operation did not complete before its timeout expired.'),
        self::NO_SUCH_WINDOW => array('NoSuchWindow', 'A request to switch to a different window could not be satisfied because the window could not be found.'),
        self::INVALID_COOKIE_DOMAIN => array('InvalidCookieDomain', 'An illegal attempt was made to set a cookie under a different domain than the current page.'),
        self::UNABLE_TO_SET_COOKIE => array('UnableToSetCookie', 'A request to set a cookie\'s value could not be satisfied.'),
        self::UNEXPECTED_ALERT_OPEN => array('UnexpectedAlertOpen', 'A modal dialog was open, blocking this operation'),
        self::NO_ALERT_OPEN_ERROR => array('NoAlertOpenError', 'An attempt was made to operate on a modal dialog when one was not open.'),
        self::SCRIPT_TIMEOUT => array('ScriptTimeout', 'A script did not complete before its timeout expired.'),
        self::INVALID_ELEMENT_COORDINATES => array('InvalidElementCoordinates', 'The coordinates provided to an interactions operation are invalid.'),
        self::IME_NOT_AVAILABLE => array('IMENotAvailable', 'IME was not available.'),
        self::IME_ENGINE_ACTIVATION_FAILED => array('IMEEngineActivationFailed', 'An IME engine could not be started.'),
        self::INVALID_SELECTOR => array('InvalidSelector', 'Argument was an invalid selector (e.g., XPath/CSS).'),
        self::SESSION_NOT_CREATED => array('SessionNotCreated', 'A new session could not be created (e.g., a required capability could not be set).'),
        self::MOVE_TARGET_OUT_OF_BOUNDS => array('MoveTargetOutOfBounds', 'Target provided for a move action is out of bounds.'),

        self::CURL_EXEC => array('CurlExec', 'curl_exec() error.'),
        self::OBSOLETE_COMMAND => array('ObsoleteCommand', 'This WebDriver command is obsolete.'),
        self::NO_PARAMETERS_EXPECTED => array('NoParametersExpected', 'This HTTP request method expects no parameters.'),
        self::JSON_PARAMETERS_EXPECTED => array('JsonParameterExpected', 'This POST request expects a JSON parameter (array).'),
        self::UNEXPECTED_PARAMETERS => array('UnexpectedParameters', 'This command does not expect this number of parameters.'),
        self::INVALID_REQUEST => array('InvalidRequest', 'This command does not support this HTTP request method.'),
        self::UNKNOWN_LOCATOR_STRATEGY => array('UnknownLocatorStrategy', 'This locator strategy is not supported.'),
        self::WEBTEST_ASSERTION => array('WebTestAssertion', 'WebTest assertion failed.'),
    );

    /**
     * Factory method to create WebDriver\Exception objects
     *
     * @param integer    $code              Code
     * @param string     $message           Message
     * @param \Exception $previousException Previous exception
     *
     * @return \Exception
     */
    public static function factory($code, $message = null, $previousException = null)
    {
        // unknown error
        if (!isset(self::$errs[$code])) {
            if (trim($message) == '') {
                $message = 'Unknown Error';
            }

            return new \Exception($message, $code, $previousException);
        }

        $errorDefinition = self::$errs[$code];

        if (trim($message) == '') {
            $message = $errorDefinition[1];
        }

        // dynamically define custom exception classes
        $className = $errorDefinition[0];
        $namespacedClassName = __CLASS__ . '\\' . $className;

        if (!class_exists($namespacedClassName, false)) {
            eval(
                'namespace ' . __CLASS__ . '; final class ' . $className . ' extends \\' . __CLASS__ . ' {}'
            );
        }

        return new $namespacedClassName($message, $code, $previousException);
    }
}
