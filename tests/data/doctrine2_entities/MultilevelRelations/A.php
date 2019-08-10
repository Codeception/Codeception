<?php

namespace MultilevelRelations;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class A
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
     * @var Collection|B[]
     *
     * @ORM\OneToMany(targetEntity="B", mappedBy="a")
     */
    private $b;

    /**
     */
    public function __construct()
    {
        $this->b = new ArrayCollection();
    }

    /**
     * @return Collection|B[]
     */
    public function getB()
    {
        return $this->b;
    }
}
