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

    /**
     * @param string|null $own
     */
    public function setOwn($own)
    {
        $this->own = $own;
    }

    /**
     * @return string|null
     */
    public function getOwn()
    {
        return $this->own;
    }
}
