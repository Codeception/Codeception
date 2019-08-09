<?php
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class CompositePrimaryKeyEntity
{
    /**
     * @var int|null
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $integerPart;

    /**
     * @var string|null
     *
     * @ORM\Id
     * @ORM\Column(type="string")
     */
    private $stringPart;

    /**
     * @param int|null $integerPart
     */
    public function setIntegerPart($integerPart)
    {
        $this->integerPart = $integerPart;
    }

    /**
     * @return int|null
     */
    public function getIntegerPart()
    {
        return $this->integerPart;
    }

    /**
     * @param string|null $stringPart
     */
    public function setStringPart($stringPart)
    {
        $this->stringPart = $stringPart;
    }

    /**
     * @return string|null
     */
    public function getStringPart()
    {
        return $this->stringPart;
    }
}
