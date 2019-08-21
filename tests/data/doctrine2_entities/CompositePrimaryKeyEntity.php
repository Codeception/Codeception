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
}
