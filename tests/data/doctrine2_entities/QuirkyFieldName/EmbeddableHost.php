<?php

namespace QuirkyFieldName;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class EmbeddableHost
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
     * @var Embeddable
     *
     * @ORM\Embedded(class="Embeddable")
     */
    private $embed;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $embedval;

    public function __construct()
    {
        $this->embed = new Embeddable;
    }
}
