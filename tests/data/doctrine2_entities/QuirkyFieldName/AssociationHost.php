<?php

namespace QuirkyFieldName;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class AssociationHost
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
     * @var Association|null
     *
     * @ORM\OneToOne(targetEntity="Association")
     */
    private $assoc;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $_assoc_val;

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param Association|null $assoc
     */
    public function setAssoc($assoc)
    {
        $this->assoc = $assoc;
    }

    /**
     * @param string|null $assoc_val
     */
    public function setAssocVal($assoc_val)
    {
        $this->_assoc_val = $assoc_val;
    }
}
