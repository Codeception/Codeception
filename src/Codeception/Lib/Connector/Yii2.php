<?php
namespace Codeception\Lib\Connector;

use Codeception\Lib\Connector\Yii2\Logger;
use Codeception\Lib\Connector\Yii2\TestMailer;
use Codeception\Util\Debug;
use Symfony\Component\BrowserKit\Client;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\BrowserKit\Response;
use Yii;
use yii\base\ExitException;
use yii\web\HttpException;
use yii\web\Response as YiiResponse;

class Yii2 extends Client
{
    use Shared\PhpSuperGlobalsConverter;

    /**
     * @var string application config file
     */
    public $configFile;

    public $defaultServerVars = [];

    /**
     * @var array
     */
    public $headers;
    public $statusCode;

    /**
     * @var \yii\web\Application
     */
    private $app;

    /**
     * @var \yii\db\Connection
     */
    public static $db; // remember the db instance

    /**
     * @var TestMailer
     */
    public static $mailer;

    /**
     * @return \yii\web\Application
     */
    public function getApplication()
    {
        if (!isset($this->app)) {
            $this->startApp();
        }
        return $this->app;
    }

    public function resetApplication()
    {
        $this->app = null;
    }

    public function startApp()
    {
        $config = require($this->configFile);
        if (!isset($config['class'])) {
            $config['class'] = 'yii\web\Application';
        }
        /** @var \yii\web\Application $app */
        $this->app = Yii::createObject($config);
        $this->persistDb();
        $this->mockMailer($config);
        \Yii::setLogger(new Logger());
    }

    public function resetPersistentVars()
    {
        static::$db = null;
        static::$mailer = null;
        \yii\web\UploadedFile::reset();
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
        $this->restoreServerVars();
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

        $app = $this->getApplication();

        $app->getResponse()->on(YiiResponse::EVENT_AFTER_PREPARE, [$this, 'processResponse']);

        // disabling logging. Logs are slowing test execution down
        foreach ($app->log->targets as $target) {
            $target->enabled = false;
        }

        $this->headers    = array();
        $this->statusCode = null;

        ob_start();

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
            
            $app->trigger($app::EVENT_BEFORE_REQUEST);
            
            $app->handleRequest($yiiRequest)->send();
            
            $app->trigger($app::EVENT_AFTER_REQUEST);
            
        } catch (\Exception $e) {
            if ($e instanceof HttpException) {
                // Don't discard output and pass exception handling to Yii to be able
                // to expect error response codes in tests.
                $app->errorHandler->discardExistingOutput = false;
                $app->errorHandler->handleException($e);
            } elseif (!$e instanceof ExitException) {
                // for exceptions not related to Http, we pass them to Codeception
                $this->resetApplication();
                throw $e;
            }
        }

        $content = ob_get_clean();

        // catch "location" header and display it in debug, otherwise it would be handled
        // by symfony browser-kit and not displayed.
        if (isset($this->headers['location'])) {
            Debug::debug("[Headers] " . json_encode($this->headers));
        }

        $this->resetApplication();

        return new Response($content, $this->statusCode, $this->headers);
    }

    protected function revertErrorHandler()
    {
        $handler = new ErrorHandler();
        set_error_handler(array($handler, 'errorHandler'));
    }


    public function restoreServerVars()
    {
        $this->server = $this->defaultServerVars;
        foreach ($this->server as $key => $value) {
            $_SERVER[$key] = $value;
        }
    }

    public function processResponse($event)
    {
        /** @var \yii\web\Response $response */
        $response = $event->sender;
        $request = Yii::$app->getRequest();
        $this->headers = $response->getHeaders()->toArray();
        $response->getHeaders()->removeAll();
        $this->statusCode = $response->getStatusCode();
        $cookies = $response->getCookies();

        if ($request->enableCookieValidation) {
            $validationKey = $request->cookieValidationKey;
        }

        foreach ($cookies as $cookie) {
            /** @var \yii\web\Cookie $cookie */
            $value = $cookie->value;
            if ($cookie->expire != 1 && isset($validationKey)) {
                $data = version_compare(Yii::getVersion(), '2.0.2', '>')
                    ? [$cookie->name, $cookie->value]
                    : $cookie->value;
                $value = Yii::$app->security->hashData(serialize($data), $validationKey);
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
        $cookies->removeAll();
    }

    /**
     * Replace mailer with in memory mailer
     * @param $config
     * @param $app
     */
    protected function mockMailer($config)
    {
        if (static::$mailer) {
            $this->app->set('mailer', static::$mailer);
            return;
        }
        
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
        
        $this->app->set('mailer', $mailerConfig);
        static::$mailer = $this->app->get('mailer');
    }

    /**
     * @param $app
     */
    protected function persistDb()
    {
        // always use the same DB connection
        if (static::$db) {
            $this->app->set('db', static::$db);
        } elseif ($this->app->has('db')) {
            static::$db = $this->app->get('db');
        }
    }
}
