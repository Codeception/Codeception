<?php

namespace CircularRelations;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="circular_c")
 */
class C
{
    /**
     * @var \CircularRelations\A
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="A", inversedBy="cs", cascade={"persist"})
     */
    private $a;

    /**
     * @var \CircularRelations\B
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="B", inversedBy="cs", cascade={"persist"})
     */
    private $b;

    /**
     * C constructor.
     *
     * @param \CircularRelations\A $a
     * @param \CircularRelations\B $b
     */
    public function __construct(\CircularRelations\A $a, \CircularRelations\B $b)
    {
        $this->a = $a;
        $this->b = $b;
    }
}
