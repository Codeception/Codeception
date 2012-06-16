<?php
namespace Codeception\PHPUnit;

class Assert extends \PHPUnit_Framework_Assert
{

    public static function assertPageContains($needle, $haystack, $message = '')
    {
        $constraint = new Constraint\Page($needle, true);
        self::assertThat($haystack, $constraint, $message);
    }

    public static function assertNotPageContains($needle, $haystack, $message = '')
    {
        $constraint = new \PHPUnit_Framework_Constraint_Not(new Constraint\Page($needle, true));
        self::assertThat($haystack, $constraint, $message);
    }

    public static function assertArrayContainsArray($needle, $haystack, $message = '')
    {
        $result = array_intersect($needle, $haystack);

        foreach ($haystack as $k)
    }




}
