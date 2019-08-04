<?php
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class PlainEntity
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
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }
}
