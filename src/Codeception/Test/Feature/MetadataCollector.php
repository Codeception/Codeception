<?php
namespace Codeception\Test\Feature;

use Codeception\Test\Metadata;

trait MetadataCollector
{
    /**
     * @var Metadata
     */
    protected $metadata;

    protected function metadataCollectorStart()
    {
        if ($incomplete = $this->getMetadata()->getIncomplete()) {
            throw new \PHPUnit_Framework_IncompleteTestError((string)$incomplete);
        }
        if ($skip = $this->getMetadata()->getSkip()) {
            throw new \PHPUnit_Framework_SkippedTestError((string)$skip);
        }
    }

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