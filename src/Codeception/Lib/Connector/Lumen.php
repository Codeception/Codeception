<?php
namespace Codeception\Lib\Connector;

use Illuminate\Http\Request;
use Laravel\Lumen\Application;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Client;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class Lumen extends Client implements HttpKernelInterface
{

    /**
     * @var Application
     */
    private $app;

    /**
     * Constructor.
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        parent::__construct($this);
    }

    /**
     * Handle a request.
     *
     * @param SymfonyRequest $request
     * @param int $type
     * @param bool $catch
     * @return Response
     */
    public function handle(SymfonyRequest $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        $this->app['request'] = $request = Request::createFromBase($request);

        $response = $this->app->handle($request);

        $method = new \ReflectionMethod(get_class($this->app), 'callTerminableMiddleware');
        $method->setAccessible(true);
        $method->invoke($this->app, $response);

        return $response;
    }
}
