<?php
namespace Codeception\Lib\Connector;

use Codeception\Lib\Connector\Yii2\Logger;
use Codeception\Lib\InnerBrowser;
use Codeception\Util\Debug;
use Symfony\Component\BrowserKit\Client;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\BrowserKit\Response;
use Yii;
use yii\base\ExitException;
use yii\base\Security;
use yii\web\Application;
use yii\web\ErrorHandler;
use yii\web\HttpException;
use yii\web\Request;
use yii\web\Response as YiiResponse;

class Yii2 extends Client
{
    use Shared\PhpSuperGlobalsConverter;

    const CLEAN_METHODS = [
        self::CLEAN_RECREATE,
        self::CLEAN_CLEAR,
        self::CLEAN_FORCE_RECREATE,
        self::CLEAN_MANUAL
    ];
    /**
     * Clean the response object by recreating it.
     * This might lose behaviors / event handlers / other changes that are done in the application bootstrap phase.
     */
    const CLEAN_RECREATE = 'recreate';
    /**
     * Same as recreate but will not warn when behaviors / event handlers are lost.
     */
    const CLEAN_FORCE_RECREATE = 'force_recreate';
    /**
     * Clean the response object by resetting specific properties via its' `clear()` method.
     * This will keep behaviors / event handlers, but could inadvertently leave some changes intact.
     * @see \Yii\web\Response::clear()
     */
    const CLEAN_CLEAR = 'clear';

    /**
     * Do not clean the response, instead the test writer will be responsible for manually resetting the response in
     * between requests during one test
     */
    const CLEAN_MANUAL = 'manual';


    /**
     * @var string application config file
     */
    public $configFile;

    /**
     * @var string method for cleaning the response object before each request
     */
    public $responseCleanMethod;

    /**
     * @var string method for cleaning the request object before each request
     */
    public $requestCleanMethod;

    /**
     * @var string[] List of component names that must be recreated before each request
     */
    public $recreateComponents = [];

    /**
     * This option is there primarily for backwards compatibility.
     * It means you cannot make any modification to application state inside your app, since they will get discarded.
     * @var bool whether to recreate the whole application before each request
     */
    public $recreateApplication = false;

    /**
     * @return \yii\web\Application
     */
    public function getApplication()
    {
        if (!isset(Yii::$app)) {
            $this->startApp();
        }
        return Yii::$app;
    }

    public function resetApplication()
    {
        codecept_debug('Destroying application');
        Yii::$app = null;
        \yii\web\UploadedFile::reset();
        if (method_exists(\yii\base\Event::className(), 'offAll')) {
            \yii\base\Event::offAll();
        }
        Yii::setLogger(null);
        // This resolves an issue with database connections not closing properly.
        gc_collect_cycles();
    }

    public function startApp()
    {
        codecept_debug('Starting application');
        $config = require($this->configFile);
        if (!isset($config['class'])) {
            $config['class'] = 'yii\web\Application';
        }

        $config = $this->mockMailer($config);
        /** @var \yii\web\Application $app */
        Yii::$app = Yii::createObject($config);

        Yii::setLogger(new Logger());
    }

    /**
     *
     * @param \Symfony\Component\BrowserKit\Request $request
     *
     * @return \Symfony\Component\BrowserKit\Response
     */
    public function doRequest($request)
    {
        $_COOKIE = $request->getCookies();
        $_SERVER = $request->getServer();
        $_FILES = $this->remapFiles($request->getFiles());
        $_REQUEST = $this->remapRequestParameters($request->getParameters());
        $_POST = $_GET = [];

        if (strtoupper($request->getMethod()) === 'GET') {
            $_GET = $_REQUEST;
        } else {
            $_POST = $_REQUEST;
        }

        $uri = $request->getUri();

        $pathString = parse_url($uri, PHP_URL_PATH);
        $queryString = parse_url($uri, PHP_URL_QUERY);
        $_SERVER['REQUEST_URI'] = $queryString === null ? $pathString : $pathString . '?' . $queryString;
        $_SERVER['REQUEST_METHOD'] = strtoupper($request->getMethod());

        parse_str($queryString, $params);
        foreach ($params as $k => $v) {
            $_GET[$k] = $v;
        }

        ob_start();

        $this->beforeRequest();

        $app = $this->getApplication();

        // disabling logging. Logs are slowing test execution down
        foreach ($app->log->targets as $target) {
            $target->enabled = false;
        }




        $yiiRequest = $app->getRequest();
        if ($request->getContent() !== null) {
            $yiiRequest->setRawBody($request->getContent());
            $yiiRequest->setBodyParams(null);
        } else {
            $yiiRequest->setRawBody(null);
            $yiiRequest->setBodyParams($_POST);
        }
        $yiiRequest->setQueryParams($_GET);

        try {
            /*
             * This is basically equivalent to $app->run() without sending the response.
             * Sending the response is problematic because it tries to send headers.
             */
            $app->trigger($app::EVENT_BEFORE_REQUEST);
            $response = $app->handleRequest($yiiRequest);
            $app->trigger($app::EVENT_AFTER_REQUEST);
            $response->send();
        } catch (\Exception $e) {
            if ($e instanceof HttpException) {
                // Don't discard output and pass exception handling to Yii to be able
                // to expect error response codes in tests.
                $app->errorHandler->discardExistingOutput = false;
                $app->errorHandler->handleException($e);
            } elseif (!$e instanceof ExitException) {
                // for exceptions not related to Http, we pass them to Codeception
                throw $e;
            }
            $response = $app->response;
        }

        $this->encodeCookies($response, $yiiRequest, $app->security);

        if ($response->isRedirection) {
            Debug::debug("[Redirect with headers]" . print_r($response->getHeaders()->toArray(), true));
        }

        $content = ob_get_clean();
        if (empty($content) && !empty($response->content)) {
            throw new \Exception('No content was sent from Yii application');
        }

        return new Response($content, $response->statusCode, $response->getHeaders()->toArray());
    }

    protected function revertErrorHandler()
    {
        $handler = new ErrorHandler();
        set_error_handler([$handler, 'errorHandler']);
    }


    /**
     * Encodes the cookies and adds them to the headers.
     * @param \yii\web\Response $response
     * @throws \yii\base\InvalidConfigException
     */
    protected function encodeCookies(
        YiiResponse $response,
        Request $request,
        Security $security
    ) {
        if ($request->enableCookieValidation) {
            $validationKey = $request->cookieValidationKey;
        }

        foreach ($response->getCookies() as $cookie) {
            /** @var \yii\web\Cookie $cookie */
            $value = $cookie->value;
            if ($cookie->expire != 1 && isset($validationKey)) {
                $data = version_compare(Yii::getVersion(), '2.0.2', '>')
                    ? [$cookie->name, $cookie->value]
                    : $cookie->value;
                $value = $security->hashData(serialize($data), $validationKey);
            }
            $c = new Cookie(
                $cookie->name,
                $value,
                $cookie->expire,
                $cookie->path,
                $cookie->domain,
                $cookie->secure,
                $cookie->httpOnly
            );
            $this->getCookieJar()->set($c);
        }
    }

    /**
     * Replace mailer with in memory mailer
     * @param array $config Original configuration
     * @return array New configuration
     */
    protected function mockMailer(array $config)
    {
        // options that make sense for mailer mock
        $allowedOptions = [
            'htmlLayout',
            'textLayout',
            'messageConfig',
            'messageClass',
            'useFileTransport',
            'fileTransportPath',
            'fileTransportCallback',
            'view',
            'viewPath',
        ];

        $mailerConfig = [
            'class' => 'Codeception\Lib\Connector\Yii2\TestMailer',
        ];

        if (isset($config['components']['mailer']) && is_array($config['components']['mailer'])) {
            foreach ($config['components']['mailer'] as $name => $value) {
                if (in_array($name, $allowedOptions, true)) {
                    $mailerConfig[$name] = $value;
                }
            }
        }
        $config['components']['mailer'] = $mailerConfig;

        return $config;
    }

    public function restart()
    {
        parent::restart();
        $this->resetApplication();
    }

    /**
     * Resets the applications' response object.
     * The method used depends on the module configuration.
     */
    protected function resetResponse(Application $app)
    {
        $method = $this->responseCleanMethod;
        // First check the current response object.
        if (($app->response->hasEventHandlers(\yii\web\Response::EVENT_BEFORE_SEND)
                || $app->response->hasEventHandlers(\yii\web\Response::EVENT_AFTER_SEND)
                || $app->response->hasEventHandlers(\yii\web\Response::EVENT_AFTER_PREPARE)
                || count($app->response->getBehaviors()) > 0
            ) && $method === self::CLEAN_RECREATE
        ) {
            Debug::debug(<<<TEXT
[WARNING] You are attaching event handlers or behaviors to the response object. But the Yii2 module is configured to recreate
the response object, this means any behaviors or events that are not attached in the component config will be lost.
We will fall back to clearing the response. If you are certain you want to recreate it, please configure 
responseCleanMethod = 'force_recreate' in the module.  
TEXT
            );
            $method = self::CLEAN_CLEAR;
        }

        switch ($method) {
            case self::CLEAN_FORCE_RECREATE:
            case self::CLEAN_RECREATE:
                $app->set('response', $app->getComponents()['response']);
                break;
            case self::CLEAN_CLEAR:
                $app->response->clear();
                break;
            case self::CLEAN_MANUAL:
                break;
        }
    }

    protected function resetRequest(Application $app)
    {
        $method = $this->requestCleanMethod;
        $request = $app->request;

        // First check the current request object.
        if (count($request->getBehaviors()) > 0 && $method === self::CLEAN_RECREATE) {
            Debug::debug(<<<TEXT
[WARNING] You are attaching event handlers or behaviors to the request object. But the Yii2 module is configured to recreate
the request object, this means any behaviors or events that are not attached in the component config will be lost.
We will fall back to clearing the request. If you are certain you want to recreate it, please configure 
requestCleanMethod = 'force_recreate' in the module.  
TEXT
            );
            $method = self::CLEAN_CLEAR;
        }

        switch ($method) {
            case self::CLEAN_FORCE_RECREATE:
            case self::CLEAN_RECREATE:
                $app->set('request', $app->getComponents()['request']);
                break;
            case self::CLEAN_CLEAR:
                $request->getHeaders()->removeAll();
                $request->setBaseUrl(null);
                $request->setHostInfo(null);
                $request->setPathInfo(null);
                $request->setScriptFile(null);
                $request->setScriptUrl(null);
                $request->setUrl(null);
                $request->setPort(null);
                $request->setSecurePort(null);
                $request->setAcceptableContentTypes(null);
                $request->setAcceptableLanguages(null);

                break;
            case self::CLEAN_MANUAL:
                break;
        }
    }

    /**
     * Called before each request, preparation happens here.
     */
    protected function beforeRequest()
    {
        if ($this->recreateApplication) {
            $this->resetApplication();
            return;
        }

        $application = $this->getApplication();

        $this->resetResponse($application);
        $this->resetRequest($application);

        $definitions = $application->getComponents(true);
        foreach ($this->recreateComponents as $component) {
            // Only recreate if it has actually been instantiated.
            if ($application->has($component, true)) {
                $application->set($component, $definitions[$component]);
            }
        }
    }
}
