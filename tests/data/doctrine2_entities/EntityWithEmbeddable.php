<?php
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class EntityWithEmbeddable
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
     * @var SampleEmbeddable
     *
     * @ORM\Embedded(class="SampleEmbeddable")
     */
    private $embed;

    /**
     */
    public function __construct()
    {
        $this->embed = new SampleEmbeddable();
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return SampleEmbeddable
     */
    public function getEmbed()
    {
        return $this->embed;
    }
}
