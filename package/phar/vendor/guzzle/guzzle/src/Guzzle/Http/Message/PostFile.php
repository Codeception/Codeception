<?php

namespace Guzzle\Http\Message;

use Guzzle\Common\Exception\InvalidArgumentException;

/**
 * POST file upload
 */
class PostFile implements PostFileInterface
{
    protected $fieldName;
    protected $contentType;
    protected $filename;

    /**
     * @param string $fieldName   Name of the field
     * @param string $filename    Path to the file
     * @param string $contentType Content-Type of the upload
     */
    public function __construct($fieldName, $filename, $contentType = null)
    {
        $this->fieldName = $fieldName;
        $this->setFilename($filename);
        $this->contentType = $contentType ?: $this->guessContentType();
    }

    /**
     * {@inheritdoc}
     */
    public function setFieldName($name)
    {
        $this->fieldName = $name;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * {@inheritdoc}
     */
    public function setFilename($filename)
    {
        // Remove leading @ symbol
        if (strpos($filename, '@') === 0) {
            $filename = substr($filename, 1);
        }

        if (!is_readable($filename)) {
            throw new InvalidArgumentException("Unable to open {$filename} for reading");
        }

        $this->filename = $filename;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * {@inheritdoc}
     */
    public function setContentType($type)
    {
        $this->contentType = $type;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurlString()
    {
        return $this->contentType
            ? '@' . $this->filename . ';type=' . $this->contentType
            : '@' . $this->filename;
    }

    /**
     * Determine the Content-Type of the file
     */
    protected function guessContentType()
    {
        // @codeCoverageIgnoreStart
        if (!class_exists('finfo', false)) {
            return 'application/octet-stream';
        }
        // @codeCoverageIgnoreEnd

        $finfo = new \finfo(FILEINFO_MIME_TYPE);

        return $finfo->file($this->filename);
    }
}
