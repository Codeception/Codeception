<?php

use Doctrine\ORM\Mapping as ORM;

/**
 * Class EntityWithCompositeIdOfRelatedEntities
 *
 * @ORM\Entity
 */
class CompositePrimaryKeyEntityWithManyToOneKeys
{
    /**
     * @var \PlainEntity
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="PlainEntity", cascade={"persist"})
     */
    private $firstComposite;

    /**
     * @var \PlainEntity
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="PlainEntity", cascade={"persist"})
     */
    private $secondComposite;

    /**
     * CompositePrimaryKeyEntityWithManyToOneKeys constructor.
     *
     * @param \PlainEntity $firstComposite
     * @param \PlainEntity $secondComposite
     */
    public function __construct(PlainEntity $firstComposite, PlainEntity $secondComposite)
    {
        $this->firstComposite  = $firstComposite;
        $this->secondComposite = $secondComposite;
    }
}
