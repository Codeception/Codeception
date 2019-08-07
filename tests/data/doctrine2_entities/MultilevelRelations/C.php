<?php

namespace MultilevelRelations;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class C
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
     * @var B
     *
     * @ORM\ManyToOne(targetEntity="B")
     */
    private $b;

    /**
     * @return B
     */
    public function getB()
    {
        return $this->b;
    }
}
