<?php

namespace Codeception\Lib\Connector;

use Exception;
use PHPUnit_Framework_AssertionFailedError;
use Symfony\Component\BrowserKit\Client;
use Symfony\Component\BrowserKit\Request as BrowserKitRequest;
use Symfony\Component\BrowserKit\Response;
use Zend\EventManager\Event;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\EventManager\SharedEventManagerInterface;
use Zend\Http\Headers as HttpHeaders;
use Zend\Http\Request as HttpRequest;
use Zend\Mvc\Application;
use Zend\Mvc\ApplicationInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\Parameters;
use Zend\Uri\Http as HttpUri;

/**
 * @author Andy O'Brien <andy@1bg.com>
 */
class ZF3 extends Client
{
    /** @var ApplicationInterface */
    private $application;

    /** @var array */
    private $applicationConfig;

    /**
     * @param array          $server
     * @param History|null   $history
     * @param CookieJar|null $cookieJar
     * @param array          $applicationConfig
     */
    public function __construct(
        array $server = [],
        History $history = null,
        CookieJar $cookieJar = null,
        array $applicationConfig = []
    ) {
        parent::__construct($server, $history, $cookieJar);

        $this->applicationConfig = $applicationConfig;
    }

    /**
     * @return void
     */
    public function createApplication(): void
    {
        $this->application    = Application::init($this->applicationConfig);
        $sendResponseListener = $this->application->getServiceManager()->get('SendResponseListener');
        $events               = $this->application->getEventManager()->getSharedManager();

        // Apigility support
        if (class_exists('ZF\Hal\Plugin\Hal')) {
            $this->addApigilitySupport($events);
        }

        $events->detach([$sendResponseListener, 'sendResponse']);
    }

    /**
     * @param BrowserKitRequest $request
     *
     * @return Response
     * @throws Exception
     */
    public function doRequest($request): Response
    {
        $applicationRequest = $this->application->getRequest();
        $uri                = new HttpUri($request->getUri());
        $queryString        = $uri->getQuery();
        $method             = strtoupper($request->getMethod());

        if (method_exists('setCookies', $applicationRequest)) {
            $applicationRequest->setCookies(new Parameters($request->getCookies()));
        }

        $query   = [];
        $post    = [];
        $content = $request->getContent();

        if ($queryString) {
            parse_str($queryString, $query);
        }

        if ($method !== HttpRequest::METHOD_GET) {
            $post = $request->getParameters();
        }

        $applicationRequest->setQuery(new Parameters($query));
        $applicationRequest->setPost(new Parameters($post));
        $applicationRequest->setFiles(new Parameters($request->getFiles()));
        $applicationRequest->setContent($content);
        $applicationRequest->setMethod($method);
        $applicationRequest->setUri($uri);

        $requestUri = $uri->getPath();
        if (!empty($queryString)) {
            $requestUri .= '?' . $queryString;
        }

        $applicationRequest->setRequestUri($requestUri);
        $applicationRequest->setHeaders($this->extractHeaders($request));

        ob_start();
        $this->application->run();
        ob_end_clean();

        $exception = $this->application->getMvcEvent()->getParam('exception');
        if ($exception instanceof Exception) {
            throw $exception;
        }

        // get the response *after* the application has run, because other ZF
        //     libraries like API Agility may *replace* the application's response
        //
        $zendResponse = $this->application->getResponse();

        // PHP's json_encode function will encode apostrophes and double quotes to unicode characters
        // We are replacing those characters here with a single quote for test assertion compatibility
        $body = str_replace(['\\u0027', '\\u0022'], "'", $zendResponse->getBody());

        $response = new Response(
            $body,
            $zendResponse->getStatusCode(),
            $zendResponse->getHeaders()->toArray()
        );

        return $response;
    }

    /**
     * @param BrowserKitRequest $request
     *
     * @return HttpHeaders
     */
    private function extractHeaders(BrowserKitRequest $request): HttpHeaders
    {
        $headers = [];
        $server  = $request->getServer();

        $contentHeaders = ['Content-Length' => true, 'Content-Md5' => true, 'Content-Type' => true];
        foreach ($server as $header => $val) {
            $header = implode('-', array_map('ucfirst', explode('-', strtolower(str_replace('_', '-', $header)))));

            if (strpos($header, 'Http-') === 0) {
                $headers[substr($header, 5)] = $val;
            } elseif (isset($contentHeaders[$header])) {
                $headers[$header] = $val;
            }
        }
        $zendHeaders = new HttpHeaders();
        $zendHeaders->addHeaders($headers);

        return $zendHeaders;
    }

    /**
     * @param $service
     *
     * @return mixed
     * @throws PHPUnit_Framework_AssertionFailedError
     */
    public function grabServiceFromContainer($service)
    {
        $serviceManager = $this->application->getServiceManager();

        if (!$serviceManager->has($service)) {
            throw new PHPUnit_Framework_AssertionFailedError("Service $service is not available in container");
        }

        return $serviceManager->get($service);
    }

    /**
     * @param $name
     * @param $service
     *
     * @return void
     */
    public function addServiceToContainer($name, $service): void
    {
        /** @var ServiceManager $serviceManager */
        $serviceManager = $this->application->getServiceManager();

        $serviceManager->setAllowOverride(true);
        $serviceManager->setService($name, $service);
        $serviceManager->setAllowOverride(false);
    }

    /**
     * @param SharedEventManagerInterface $events
     *
     * @return void
     */
    private function addApigilitySupport(SharedEventManagerInterface $events): void
    {
        $listener = new class {
            use ListenerAggregateTrait;

            /**
             * @param SharedEventManagerInterface $events
             *
             * @return void
             */
            public function attachShared(SharedEventManagerInterface $events)
            {
                $this->listeners[] = $events->attach(
                    'ZF\Hal\Plugin\Hal',
                    'renderCollection',
                    function (Event $event) {
                        $params = $event->getTarget()->getController()->getResource()->getRouteMatch()->getParams();

                        /** @var ZF\Hal\Collection $target */
                        $target = $event->getParam('collection');

                        $target->setCollectionRouteParams(
                            array_merge(
                                $target->getCollectionRouteParams(),
                                $params
                            )
                        );

                        /** @var ZF\Hal\Link\Link $link */
                        foreach ($target->getLinks() as $link) {
                            $link->setRouteParams(array_merge($link->getRouteParams(), $params));
                        }
                    },
                    -10000
                );

                $this->listeners[] = $events->attach(
                    'ZF\Hal\Plugin\Hal',
                    'renderEntity',
                    function (Event $event) {
                        $params = $event->getTarget()->getController()->getResource()->getRouteMatch()->getParams();

                        /** @var ZF\Hal\Entity $target */
                        $target = $event->getParam('entity');

                        /** @var ZF\Hal\Link\Link $link */
                        foreach ($target->getLinks() as $link) {
                            $link->setRouteParams(array_merge($link->getRouteParams(), $params));
                        }
                    },
                    -10000
                );
            }
        };

        $listener->attachShared($events);
    }
}
