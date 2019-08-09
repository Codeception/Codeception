<?php
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class NonTypicalPrimaryKeyEntity
{
    /**
     * @var int|null
     *
     * @ORM\Id
     * @ORM\Column(type="string")
     */
    private $primaryKey;

    /**
     * @param int|null $primaryKey
     */
    public function setPrimaryKey($primaryKey)
    {
        $this->primaryKey = $primaryKey;
    }

    /**
     * @return int|null
     */
    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }
}
