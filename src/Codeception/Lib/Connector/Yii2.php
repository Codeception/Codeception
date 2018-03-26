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
use yii\web\HttpException;
use yii\web\Request;
use yii\web\Response as YiiResponse;

class Yii2 extends Client
{
    use Shared\PhpSuperGlobalsConverter;

    /**
     * @var string application config file
     */
    public $configFile;

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

        $app = $this->getApplication();

        /**
         * Just before the request we set the response object so it is always fresh.
         * @todo Implement some kind of check to see if someone tried to change the objects' properties and expects
         * those changes to be reflected in the reponse.
         */
        $app->set('response', $app->getComponents()['response']);

        // disabling logging. Logs are slowing test execution down
        foreach ($app->log->targets as $target) {
            $target->enabled = false;
        }

        ob_start();

        // recreating request object to reset headers and cookies collections
        /**
         * Just before the request we set the request object so it is always fresh.
         * @todo Implement some kind of check to see if someone tried to change the objects' properties and expects
         * those changes to be reflected in the reponse.
         */
        $app->set('request', $app->getComponents()['request']);

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
            codecept_debug($response->isSent);
            $response->send();
        } catch (\Exception $e) {
            if ($e instanceof HttpException) {
                // Don't discard output and pass exception handling to Yii to be able
                // to expect error response codes in tests.
                $app->errorHandler->discardExistingOutput = false;
                $app->errorHandler->handleException($e);
                $response = $app->response;

            } elseif (!$e instanceof ExitException) {
                // for exceptions not related to Http, we pass them to Codeception
                $this->resetApplication();
                throw $e;
            }
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

    /**
     * A new client is created for every test, it is destroyed after every test.
     * @see InnerBrowser::_after()
     *
     */
    public function __destruct()
    {
        $this->resetApplication();
    }

    public function restart()
    {
        parent::restart();
        $this->resetApplication();
    }
}
