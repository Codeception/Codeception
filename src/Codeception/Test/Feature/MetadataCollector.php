<?php

declare(strict_types=1);

namespace Codeception\Test\Feature;

use Codeception\Test\Metadata;

trait MetadataCollector
{
    /**
     * @var Metadata
     */
    private $metadata;

    protected function setMetadata(Metadata $metadata): void
    {
        $this->metadata = $metadata;
    }

    public function getMetadata(): Metadata
    {
        return $this->metadata;
    }

    public function getName()
    {
        return $this->getMetadata()->getName();
    }

    public function getFileName()
    {
        return $this->getMetadata()->getFilename();
    }
}
