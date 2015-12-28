<?php
namespace Codeception\Test\Feature;

use Codeception\Test\Metadata;

trait MetadataCollector
{
    /**
     * @var Metadata
     */
    protected $metadata;

    public function getMetadata()
    {
        if (!$this->metadata) {
            $this->metadata = new Metadata();
        }
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