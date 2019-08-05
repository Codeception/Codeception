<?php
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class NonTypicalPrimaryKeyEntity
{
    /**
     * @var int|null
     *
     * @ORM\Id
     * @ORM\Column(type="string")
     */
    private $primaryKey;
}
