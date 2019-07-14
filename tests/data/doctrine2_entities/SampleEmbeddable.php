<?php
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Embeddable
 */
class SampleEmbeddable
{
    /**
     * @var string|null
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $val;
}
