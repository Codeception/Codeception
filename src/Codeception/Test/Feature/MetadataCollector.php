<?php

declare(strict_types=1);

namespace Codeception\Test\Feature;

use Codeception\Test\Metadata;

trait MetadataCollector
{
    private Metadata $metadata;

    protected function setMetadata(Metadata $metadata): void
    {
        $this->metadata = $metadata;
    }

    public function getMetadata(): Metadata
    {
        return $this->metadata;
    }

    public function getName(): string
    {
        return $this->getMetadata()->getName();
    }

    public function getFileName(): string
    {
        return $this->getMetadata()->getFilename();
    }
}
