<?php

namespace Guzzle\Http\Message;

use Guzzle\Http\Exception\RequestException;
use Guzzle\Http\EntityBodyInterface;
use Guzzle\Http\QueryString;

/**
 * HTTP request that sends an entity-body in the request message (POST, PUT)
 */
interface EntityEnclosingRequestInterface extends RequestInterface
{
    const URL_ENCODED = 'application/x-www-form-urlencoded';
    const MULTIPART = 'multipart/form-data';

    /**
     * Set the body of the request
     *
     * @param string|resource|EntityBodyInterface $body               Body to use in the entity body of the request
     * @param string                              $contentType        Content-Type to set. Leave null to use an existing
     *                                                                Content-Type or to guess the Content-Type
     * @param bool                                $tryChunkedTransfer Try to use chunked Transfer-Encoding
     *
     * @return EntityEnclosingRequestInterface
     * @throws RequestException if the protocol is < 1.1 and Content-Length can not be determined
     */
    public function setBody($body, $contentType = null, $tryChunkedTransfer = false);

    /**
     * Get the body of the request if set
     *
     * @return EntityBodyInterface|null
     */
    public function getBody();

    /**
     * Get a POST field from the request
     *
     * @param string $field Field to retrieve
     *
     * @return mixed|null
     */
    public function getPostField($field);

    /**
     * Get the post fields that will be used in the request
     *
     * @return QueryString
     */
    public function getPostFields();

    /**
     * Set a POST field value
     *
     * @param string $key   Key to set
     * @param string $value Value to set
     *
     * @return EntityEnclosingRequestInterface
     */
    public function setPostField($key, $value);

    /**
     * Add POST fields to use in the request
     *
     * @param QueryString|array $fields POST fields
     *
     * @return EntityEnclosingRequestInterface
     */
    public function addPostFields($fields);

    /**
     * Remove a POST field or file by name
     *
     * @param string $field Name of the POST field or file to remove
     *
     * @return EntityEnclosingRequestInterface
     */
    public function removePostField($field);

    /**
     * Returns an associative array of POST field names to PostFileInterface objects
     *
     * @return array
     */
    public function getPostFiles();

    /**
     * Get a POST file from the request
     *
     * @param string $fieldName POST fields to retrieve
     *
     * @return array|null Returns an array wrapping an array of PostFileInterface objects
     */
    public function getPostFile($fieldName);

    /**
     * Remove a POST file from the request
     *
     * @param string $fieldName POST file field name to remove
     *
     * @return EntityEnclosingRequestInterface
     */
    public function removePostFile($fieldName);

    /**
     * Add a POST file to the upload
     *
     * @param string $field       POST field to use (e.g. file). Used to reference content from the server.
     * @param string $filename    Full path to the file. Do not include the @ symbol.
     * @param string $contentType Optional Content-Type to add to the Content-Disposition.
     *                            Default behavior is to guess. Set to false to not specify.
     *
     * @return EntityEnclosingRequestInterface
     */
    public function addPostFile($field, $filename = null, $contentType = null);

    /**
     * Add POST files to use in the upload
     *
     * @param array $files An array of POST fields => filenames where filename can be a string or PostFileInterface
     *
     * @return EntityEnclosingRequestInterface
     */
    public function addPostFiles(array $files);
}
