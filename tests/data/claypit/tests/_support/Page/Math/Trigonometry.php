<?php
namespace Page\Math;

class Trigonometry
{
    // include url of current page
    public static $URL = '';

    /**
     * Declare UI map for this page here. CSS or XPath allowed.
     * public static $usernameField = '#username';
     * public static $formSubmitButton = "#mainForm input[type=submit]";
     */

    /**
     * Basic route example for your current URL
     * You can append any additional parameter to URL
     * and use it in tests like: Page\Edit::route('/123-post');
     */
    public static function route($param)
    {
        return static::$URL.$param;
    }

    /**
     * @var \MathTester;
     */
    protected $mathTester;

    public function __construct(\MathTester $I)
    {
        $this->mathTester = $I;
    }

    public function tan($arg)
    {
        $this->mathTester->expect('i get tan of '.$arg);
        return tan($arg);
    }

    public function assertTanIsLessThen($tan, $val)
    {
        $this->mathTester->assertLessThan($val, $this->tan($tan));

    }

}