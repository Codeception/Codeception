<?php

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class EntityWithConstructorParameters
{
    /**
     * @var int|null
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $name;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $foo;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $bar;

    public function __construct($name, $foo = null, $bar = 'foobar')
    {
        $this->name = $name;
        $this->foo = $foo;
        $this->bar = $bar;
    }
}
