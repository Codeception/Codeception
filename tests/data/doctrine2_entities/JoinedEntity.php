<?php
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class JoinedEntity extends JoinedEntityBase
{

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $own;
}
