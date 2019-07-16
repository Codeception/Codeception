<?php

namespace QuirkyFieldName;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Association
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
    private $val;

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }
}
