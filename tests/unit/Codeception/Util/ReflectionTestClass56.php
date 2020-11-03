<?php
namespace Codeception\Util;

use Codeception\Codecept;

class ReflectionTestClass
{
    const FOO = 'bar';

    private $value = 'test';
    protected $obj = null;
    static $flavorOfTheWeek = '';

    public function setInt($i)
    {
        $this->value = (string)$i;

        return $this;
    }

    public function setMixed($m)
    {
        $this->value = (string)$m;

        return $this;
    }

    public function setValue($s)
    {
        $this->value = $s;

        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setDebug(Debug $obj, $flavor = self::FOO)
    {
        $this->obj = $obj;
        self::$flavorOfTheWeek = $flavor;

        return $this;
    }

    static public function setFlavor($flavor = self::FOO)
    {
        self::$flavorOfTheWeek = $flavor;
    }

    public function setFlavorImportedDefault($flavor = Codecept::VERSION)
    {
        self::$flavorOfTheWeek = $flavor;
    }

    private function getSecret($s)
    {
        return sprintf("I'm a %s!", $s);
    }
}