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

    /**
     * @param string|null $val
     */
    public function setVal($val)
    {
        $this->val = $val;
    }

    /**
     * @return string|null
     */
    public function getVal()
    {
        return $this->val;
    }
}
