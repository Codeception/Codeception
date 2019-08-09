<?php

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class QuirkySetters
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
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    public function getCustomProperty($property)
    {
        return $this->{$property};
    }

    public function setCustomProperty($property, $value)
    {
        $this->{$property} = '[c]' . $value;
    }

    /**
     * @param string|null $name
     */
    public function setName($name)
    {
        $this->name = '[set]' . $name;
    }

    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }
}
