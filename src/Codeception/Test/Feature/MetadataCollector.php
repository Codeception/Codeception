<?php
namespace Codeception\Test\Feature;

use Codeception\Test\Metadata;

trait MetadataCollector
{
    /**
     * @var Metadata
     */
    private $metadata;

    protected function setMetadata(Metadata $metadata)
    {
        $this->metadata = $metadata;
    }

    public function getMetadata()
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
