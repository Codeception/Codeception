<?php

namespace Guzzle\Http;

use Guzzle\Common\Collection;
use Guzzle\Common\AbstractHasDispatcher;
use Guzzle\Common\Exception\ExceptionCollection;
use Guzzle\Common\Exception\InvalidArgumentException;
use Guzzle\Parser\ParserRegistry;
use Guzzle\Parser\UriTemplate\UriTemplateInterface;
use Guzzle\Http\Message\RequestInterface;
use Guzzle\Http\Message\RequestFactory;
use Guzzle\Http\Message\RequestFactoryInterface;
use Guzzle\Http\Curl\CurlMultiInterface;
use Guzzle\Http\Curl\CurlMulti;
use Guzzle\Http\Curl\CurlHandle;

/**
 * HTTP client
 */
class Client extends AbstractHasDispatcher implements ClientInterface
{
    /**
     * @var Collection Default HTTP headers to set on each request
     */
    protected $defaultHeaders;

    /**
     * @var Collection Parameter object holding configuration data
     */
    private $config;

    /**
     * @var Url Base URL of the client
     */
    private $baseUrl;

    /**
     * @var CurlMultiInterface CurlMulti object used internally
     */
    private $curlMulti;

    /**
     * @var UriTemplateInterface URI template owned by the client
     */
    private $uriTemplate;

    /**
     * @var RequestFactoryInterface Request factory used by the client
     */
    protected $requestFactory;

    /**
     * {@inheritdoc}
     */
    public static function getAllEvents()
    {
        return array(self::CREATE_REQUEST);
    }

    /**
     * Client constructor
     *
     * @param string           $baseUrl Base URL of the web service
     * @param array|Collection $config  Configuration settings
     */
    public function __construct($baseUrl = '', $config = null)
    {
        $this->setConfig($config ?: new Collection());
        $this->setBaseUrl($baseUrl);
        $this->defaultHeaders = new Collection();
        $this->setRequestFactory(RequestFactory::getInstance());
    }

    /**
     * {@inheritdoc}
     */
    final public function setConfig($config)
    {
        // Set the configuration object
        if ($config instanceof Collection) {
            $this->config = $config;
        } elseif (is_array($config)) {
            $this->config = new Collection($config);
        } else {
            throw new InvalidArgumentException(
                'Config must be an array or Collection'
            );
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    final public function getConfig($key = false)
    {
        return $key ? $this->config->get($key) : $this->config;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultHeaders()
    {
        return $this->defaultHeaders;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultHeaders($headers)
    {
        if ($headers instanceof Collection) {
            $this->defaultHeaders = $headers;
        } elseif (is_array($headers)) {
            $this->defaultHeaders = new Collection($headers);
        } else {
            throw new InvalidArgumentException('Headers must be an array or Collection');
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function expandTemplate($template, array $variables = null)
    {
        $expansionVars = $this->getConfig()->getAll();
        if ($variables) {
            $expansionVars = array_merge($expansionVars, $variables);
        }

        return $this->getUriTemplate()->expand($template, $expansionVars);
    }

    /**
     * {@inheritdoc}
     */
    public function setUriTemplate(UriTemplateInterface $uriTemplate)
    {
        $this->uriTemplate = $uriTemplate;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUriTemplate()
    {
        if (!$this->uriTemplate) {
            $this->uriTemplate = ParserRegistry::get('uri_template');
        }

        return $this->uriTemplate;
    }

    /**
     * {@inheritdoc}
     */
    public function createRequest($method = RequestInterface::GET, $uri = null, $headers = null, $body = null)
    {
        if (!is_array($uri)) {
            $templateVars = null;
        } else {
            if (count($uri) != 2 || !is_array($uri[1])) {
                throw new InvalidArgumentException(
                    'You must provide a URI template followed by an array of template variables '
                    . 'when using an array for a URI template'
                );
            }
            list($uri, $templateVars) = $uri;
        }

        if (!$uri) {
            $url = $this->getBaseUrl();
        } elseif (substr($uri, 0, 4) === 'http') {
            // Use absolute URLs as-is
            $url = $this->expandTemplate($uri, $templateVars);
        } else {
            $url = Url::factory($this->getBaseUrl())->combine($this->expandTemplate($uri, $templateVars));
        }

        // If default headers are provided, then merge them into existing headers
        // If a collision occurs, the header is completely replaced
        if (count($this->defaultHeaders)) {
            if ($headers instanceof Collection) {
                $headers = array_merge($this->defaultHeaders->getAll(), $headers->getAll());
            } elseif (is_array($headers)) {
                 $headers = array_merge($this->defaultHeaders->getAll(), $headers);
            } else {
                $headers = $this->defaultHeaders;
            }
        }

        return $this->prepareRequest(
            $this->requestFactory->create($method, (string) $url, $headers, $body)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseUrl($expand = true)
    {
        return $expand ? $this->expandTemplate($this->baseUrl) : $this->baseUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseUrl($url)
    {
        $this->baseUrl = $url;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setUserAgent($userAgent, $includeDefault = false)
    {
        if ($includeDefault) {
            $userAgent .= ' ' . Utils::getDefaultUserAgent();
        }
        $this->defaultHeaders->set('User-Agent', $userAgent);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function get($uri = null, $headers = null, $body = null)
    {
        return $this->createRequest('GET', $uri, $headers, $body);
    }

    /**
     * {@inheritdoc}
     */
    public function head($uri = null, $headers = null)
    {
        return $this->createRequest('HEAD', $uri, $headers);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($uri = null, $headers = null, $body = null)
    {
        return $this->createRequest('DELETE', $uri, $headers, $body);
    }

    /**
     * {@inheritdoc}
     */
    public function put($uri = null, $headers = null, $body = null)
    {
        return $this->createRequest('PUT', $uri, $headers, $body);
    }

    /**
     * {@inheritdoc}
     */
    public function patch($uri = null, $headers = null, $body = null)
    {
        return $this->createRequest('PATCH', $uri, $headers, $body);
    }

    /**
     * {@inheritdoc}
     */
    public function post($uri = null, $headers = null, $postBody = null)
    {
        return $this->createRequest('POST', $uri, $headers, $postBody);
    }

    /**
     * {@inheritdoc}
     */
    public function options($uri = null)
    {
        return $this->createRequest('OPTIONS', $uri);
    }

    /**
     * {@inheritdoc}
     */
    public function send($requests)
    {
        $curlMulti = $this->getCurlMulti();
        $multipleRequests = !($requests instanceof RequestInterface);
        if (!$multipleRequests) {
            $requests = array($requests);
        }

        foreach ($requests as $request) {
            $curlMulti->add($request);
        }

        try {
            $curlMulti->send();
        } catch (ExceptionCollection $e) {
            throw $multipleRequests ? $e : $e->getIterator()->offsetGet(0);
        }

        if (!$multipleRequests) {
            return end($requests)->getResponse();
        } else {
            return array_map(function($request) {
                return $request->getResponse();
            }, $requests);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setCurlMulti(CurlMultiInterface $curlMulti)
    {
        $this->curlMulti = $curlMulti;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurlMulti()
    {
        if (!$this->curlMulti) {
            $this->curlMulti = CurlMulti::getInstance();
        }

        return $this->curlMulti;
    }

    /**
     * {@inheritdoc}
     */
    public function setRequestFactory(RequestFactoryInterface $factory)
    {
        $this->requestFactory = $factory;

        return $this;
    }

    /**
     * Prepare a request to be sent from the Client by adding client specific
     * behaviors and properties to the request.
     *
     * @param RequestInterface $request Request to prepare for the client
     *
     * @return RequestInterface
     */
    protected function prepareRequest(RequestInterface $request)
    {
        $request->setClient($this);
        // Add any curl options to the request
        $request->getCurlOptions()->merge(CurlHandle::parseCurlConfig($this->config));

        foreach ($this->config as $key => $value) {
            if (strpos($key, 'params.') === 0) {
                // Add request specific parameters to all requests (prefix with 'params.')
                $request->getParams()->set(substr($key, 7), $value);
            }
        }

        // Attach client observers to the request
        $request->setEventDispatcher(clone $this->getEventDispatcher());

        $this->dispatch('client.create_request', array(
            'client'  => $this,
            'request' => $request
        ));

        return $request;
    }
}
