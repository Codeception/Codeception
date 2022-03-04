<?php

declare(strict_types=1);

namespace Codeception\Util;

use Codeception\Codecept;

class ReflectionTestClass
{
    /**
     * @var string
     */
    public const FOO = 'bar';

    private string $value = 'test';

    protected ?Debug $obj = null;

    public static string $flavorOfTheWeek = '';

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

    public function setDebug(Debug $obj, string $flavor = self::FOO): self
    {
        $this->obj = $obj;
        self::$flavorOfTheWeek = $flavor;

        return $this;
    }

    public static function setFlavor(string $flavor = self::FOO)
    {
        self::$flavorOfTheWeek = $flavor;
    }

    public function setFlavorImportedDefault(string $flavor = Codecept::VERSION)
    {
        self::$flavorOfTheWeek = $flavor;
    }

    private function getSecret(string $s): string
    {
        return sprintf("I'm a %s!", $s);
    }
}
