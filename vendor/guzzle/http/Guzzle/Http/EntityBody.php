<?php

namespace Guzzle\Http;

use Guzzle\Common\Stream;
use Guzzle\Common\Exception\InvalidArgumentException;

/**
 * Entity body used with an HTTP request or response
 */
class EntityBody extends Stream implements EntityBodyInterface
{
    /**
     * @var bool Content-Encoding of the entity body if known
     */
    protected $contentEncoding = false;

    /**
     * Create a new EntityBody based on the input type
     *
     * @param resource|string|EntityBody $resource Entity body data
     * @param int                        $size     Size of the data contained in the resource
     *
     * @return EntityBody
     * @throws InvalidArgumentException if the $resource arg is not a resource or string
     */
    public static function factory($resource = '', $size = null)
    {
        if ($resource instanceof EntityBodyInterface) {
            return $resource;
        }

        switch (gettype($resource)) {
            case 'string':
                return self::fromString($resource);
            case 'resource':
                return new static($resource, $size);
            case 'object':
                if (method_exists($resource, '__toString')) {
                    return self::fromString((string) $resource);
                }
                break;
            case 'array':
                return self::fromString(http_build_query($resource));
        }

        throw new InvalidArgumentException('Invalid resource type');
    }

    /**
     * Create a new EntityBody from a string
     *
     * @param string $string String of data
     *
     * @return EntityBody
     */
    public static function fromString($string)
    {
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $string);
        rewind($stream);

        return new static($stream);
    }

    /**
     * {@inheritdoc}
     */
    public function compress($filter = 'zlib.deflate')
    {
        $result = $this->handleCompression($filter);
        $this->contentEncoding = $result ? $filter : false;

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function uncompress($filter = 'zlib.inflate')
    {
        $offsetStart = 0;

        // When inflating gzipped data, the first 10 bytes must be stripped
        // if a gzip header is present
        if ($filter == 'zlib.inflate') {
            // @codeCoverageIgnoreStart
            if (!$this->isReadable() || ($this->isConsumed() && !$this->isSeekable())) {
                return false;
            }
            // @codeCoverageIgnoreEnd
            if (stream_get_contents($this->stream, 3, 0) === "\x1f\x8b\x08") {
                $offsetStart = 10;
            }
        }

        $this->contentEncoding = false;

        return $this->handleCompression($filter, $offsetStart);
    }

    /**
     * {@inheritdoc}
     */
    public function getContentLength()
    {
        return $this->getSize();
    }

    /**
     * {@inheritdoc}
     */
    public function getContentType()
    {
        if (!class_exists('finfo', false) || !($this->isLocal() && $this->getWrapper() == 'plainfile' && file_exists($this->getUri()))) {
            return 'application/octet-stream';
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);

        return $finfo->file($this->getUri());
    }

    /**
     * {@inheritdoc}
     */
    public function getContentMd5($rawOutput = false, $base64Encode = false)
    {
        return self::calculateMd5($this, $rawOutput, $base64Encode);
    }

    /**
     * Calculate the MD5 hash of an entity body
     *
     * @param EntityBodyInterface $body         Entity body to calcutate the hash for
     * @param bool                $rawOutput    Whether or not to use raw output
     * @param bool                $base64Encode Whether or not to base64 encode raw output (only if raw output is true)
     *
     * @return bool|string Returns an MD5 string on success or FALSE on failure
     */
    public static function calculateMd5(EntityBodyInterface $body, $rawOutput = false, $base64Encode = false)
    {
        $pos = $body->ftell();
        if (!$body->seek(0)) {
            return false;
        }

        $ctx = hash_init('md5');
        while ($data = $body->read(1024)) {
            hash_update($ctx, $data);
        }

        $out = hash_final($ctx, (bool) $rawOutput);
        $body->seek($pos);

        return ((bool) $base64Encode && (bool) $rawOutput) ? base64_encode($out) : $out;
    }

    /**
     * {@inheritdoc}
     */
    public function setStreamFilterContentEncoding($streamFilterContentEncoding)
    {
        $this->contentEncoding = $streamFilterContentEncoding;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getContentEncoding()
    {
        return strtr($this->contentEncoding, array(
            'zlib.deflate' => 'gzip',
            'bzip2.compress' => 'compress'
        )) ?: false;
    }

    /**
     * {@inheritdoc}
     */
    protected function handleCompression($filter, $offsetStart = 0)
    {
        // @codeCoverageIgnoreStart
        if (!$this->isReadable() || ($this->isConsumed() && !$this->isSeekable())) {
            return false;
        }
        // @codeCoverageIgnoreEnd

        $handle = fopen('php://temp', 'r+');
        $filter = @stream_filter_append($handle, $filter, STREAM_FILTER_WRITE);
        if (!$filter) {
            return false;
        }

        // Seek to the offset start if possible
        $this->seek($offsetStart);
        while ($data = fread($this->stream, 8096)) {
            fwrite($handle, $data);
        }

        fclose($this->stream);
        $this->stream = $handle;
        stream_filter_remove($filter);
        $stat = fstat($this->stream);
        $this->size = $stat['size'];
        $this->rebuildCache();
        $this->seek(0);

        return true;
    }
}
