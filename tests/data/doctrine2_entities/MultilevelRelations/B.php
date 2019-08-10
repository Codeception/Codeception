<?php

namespace MultilevelRelations;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class B
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
     * @var A
     *
     * @ORM\ManyToOne(targetEntity="A")
     */
    private $a;

    /**
     * @var Collection|C[]
     *
     * @ORM\OneToMany(targetEntity="C", mappedBy="b")
     */
    private $c;

    /**
     */
    public function __construct()
    {
        $this->c = new ArrayCollection();
    }

    /**
     * @return A
     */
    public function getA()
    {
        return $this->a;
    }

    /**
     * @return Collection|C[]
     */
    public function getC()
    {
        return $this->c;
    }
}
