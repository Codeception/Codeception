<?php
namespace Codeception\Util;

use Codeception\Codecept;

class ReflectionTestClass
{
    const FOO = 'bar';

    private $value = 'test';
    protected $obj = null;
    static $flavorOfTheWeek = '';

    public function setInt(int $i): self
    {
        $this->value = (string)$i;

        return $this;
    }

    public function setMixed($m): self
    {
        $this->value = (string)$m;

        return $this;
    }

    public function setValue(string $s): self
    {
        $this->value = $s;

        return $this;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setDebug(Debug $obj, $flavor = self::FOO): self
    {
        $this->obj = $obj;
        self::$flavorOfTheWeek = $flavor;

        return $this;
    }

    static public function setFlavor(string $flavor = self::FOO): void
    {
        self::$flavorOfTheWeek = $flavor;
    }

    public function setFlavorImportedDefault(string $flavor = Codecept::VERSION): void
    {
        self::$flavorOfTheWeek = $flavor;
    }

    private function getSecret(string $s): string
    {
        return sprintf("I'm a %s!", $s);
    }
}