<?php
namespace Codeception\Module;

/**
 * Very basic module for functional testing of any web application which is accessed by one index.php file.
 * This helps you to interact with your application without webserver running. It emulates GET and POST requests,
 * sends to your application and buffers the response.
 *
 * Please, note that your web application will be running inside a Codeception process.
 * If you get a conflicts running it this way, consider using acceptance testing with PhpBrowser.
 *
 * If you are using one of popular PHP frameworks, better to use the module for specific framework, instead of this one.
 * If module for your favorite PHP framework, doesn't exist you can write it yourself!
 *
 * This module shares the Framework DSL Interface
 *
 * ## Config
 *
 *
 *
 *
 *
 */


class Functional extends \Codeception\Util\Framework
{
    protected $requiredFields = array('index');



}
