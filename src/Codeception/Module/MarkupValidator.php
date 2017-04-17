<?php
namespace Codeception\Module;

use Exception;
use stdClass;
use Codeception\Lib\Interfaces\PageSourceViewer;
use Codeception\Module;
use GuzzleHttp\Client;

/**
 * A module which validates current page markup via the W3C Markup Validation Service.
 * Requires either the `PhpBrowser` or the `WebDriver` module.
 *
 * Configuration options:
 *  - pageSourceViewer
 *  - ignoreWarnings
 *  - ignoredErrors
 */
class MarkupValidator extends Module
{
    const W3C_MARKUP_VALIDATION_SERVICE_BASE_URI = 'https://validator.w3.org';

    const W3C_MARKUP_VALIDATION_SERVICE_ENDPOINT = '/nu/';

    /**
     * Validates current page markup via the W3C Markup Validation Service.
     *
     * @param bool|null $ignoreWarnings Whether to ignore warnings or not.
     * If `null`, module-wide value is used. If module-wide value is `null` too
     * then warnings are not ignored by default.
     */
    public function validateMarkup($ignoreWarnings = null)
    {
        $markup = $this->getCurrentPageMarkup();
        $validationData = $this->sendMarkupValidationRequest($markup);
        foreach ($validationData->messages as $message) {
            $this->processMarkupValidationMessage($message, $ignoreWarnings);
        }
    }

    /**
     * Returns current page markup.
     *
     * @return string Current page markup.
     */
    protected function getCurrentPageMarkup()
    {
        $pageViewerConfigKey = 'pageSourceViewer';
        if (!isset($this->config[$pageViewerConfigKey])) {
            throw new Exception(sprintf(
                'Missing mandatory configuration parameter "%s".',
                $pageViewerConfigKey
            ));
        }

        $pageViewerName = $this->config[$pageViewerConfigKey];
        if (!$this->hasModule($pageViewerName)) {
            throw new Exception(sprintf(
                '"%s" page viewer was not found.',
                $pageViewerName
            ));
        }

        /* @var $pageViewer PageSourceViewer */
        $pageViewer = $this->getModule($pageViewerName);
        $pageMarkup = $pageViewer->_getPageSource();

        return $pageMarkup;
    }

    /**
     * Send a markup validation request to the W3C Markup Validation Service
     * and returns response data.
     *
     * @param string $markup Page markup to validate.
     * @return stdClass W3C Markup Validation Service response data.
     */
    protected function sendMarkupValidationRequest($markup)
    {
        $сlient = new Client([
            'base_uri' => self::W3C_MARKUP_VALIDATION_SERVICE_BASE_URI,
        ]);
        $reponse = $сlient->post(self::W3C_MARKUP_VALIDATION_SERVICE_ENDPOINT, [
            'headers' => [
                'Content-Type' => 'text/html; charset=UTF-8;',
            ],
            'query' => [
                'out' => 'json',
            ],
            'body' => $markup,
        ]);
        $responseContents = $reponse->getBody()->getContents();
        $responseData = json_decode($responseContents);
        if ($responseData === null) {
            throw new Exception('Unable to parse W3C Markup Validation Service response.');
        }

        return $responseData;
    }

    /**
     * Processes a document markup validation message.
     *
     * @param stdClass $message Markup validation message.
     * @param bool|null $ignoreWarnings Whether to ignore warnings or not.
     */
    protected function processMarkupValidationMessage(stdClass $message, $ignoreWarnings)
    {
        $type = $message->type;
        $summary = $message->message;
        $details = isset($message->extract)
                    ? $message->extract
                    : 'unavailable';
        if ($type === 'error' ||
            $type === 'info' && !$this->getIgnoreWarnings($ignoreWarnings)
        ) {
            $errorIsIgnored = $this->getErrorIsIgnored($summary);
            if (!$errorIsIgnored) {
                $this->reportMarkupValidationError($summary, $details);
            }
        }
    }

    /**
     * Reports a document markup validation error.
     *
     * @param string $summary Markup validation error summary.
     * @param string $details Markup validation error details.
     */
    protected function reportMarkupValidationError($summary, $details)
    {
        $template = 'Markup validation error. %s. Details: %s';
        $message = sprintf($template, $summary, $details);
        $this->fail($message);
    }

    /**
     * Returns an actual value of the `ignoreWarnings` parameter.
     * If local value is `null`, module-wide value is used.
     * If module-wide value is `null` too then warnings are not ignored by default.
     * @param bool|null $ignoreWarnings A local value of the `ignoreWarnings` parameter.
     */
    private function getIgnoreWarnings($ignoreWarnings)
    {
        if (is_bool($ignoreWarnings)) {
            return $ignoreWarnings;
        }

        $ignoreWarningsConfigKey = 'ignoreWarnings';
        if (isset($this->config[$ignoreWarningsConfigKey]) &&
            is_bool($this->config[$ignoreWarningsConfigKey])
        ) {
            return $this->config[$ignoreWarningsConfigKey];
        }

        return false;
    }

    /**
     * Returns a boolean indicating whether an error is ignored or not.
     *
     * @param string $summary Error summary.
     * @return boolean Whether an error is ignored or not.
     */
    private function getErrorIsIgnored($summary)
    {
        $ignoredErrorsConfigKey = 'ignoredErrors';
        if (!isset($this->config[$ignoredErrorsConfigKey]) ||
            !is_array($this->config[$ignoredErrorsConfigKey])
        ) {
            return false;
        }

        $ignoredErrors = $this->config[$ignoredErrorsConfigKey];
        foreach ($ignoredErrors as $ignoredError) {
            $erorIsIgnored = preg_match($ignoredError, $summary) === 1;
            if ($erorIsIgnored) {
                return true;
            }
        }

        return false;
    }
}
