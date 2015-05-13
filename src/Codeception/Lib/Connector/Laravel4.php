<?php
namespace Codeception\Lib\Connector;

use Illuminate\Foundation\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Client;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;

class Laravel4 extends Client implements HttpKernelInterface, TerminableInterface
{

    /**
     * @var Application
     */
    private $app;

    /**
     * @var HttpKernelInterface
     */
    private $httpKernel;

    /**
     * Constructor.
     *
     * @param Application $app
     */
    public function  __construct(Application $app)
    {
        $this->app = $app;
        $this->app->boot();
        $this->httpKernel = $this->getStackedClient();

        parent::__construct($this);
    }

    /**
     * Handle a request.
     *
     * @param Request $request
     * @param int $type
     * @param bool $catch
     * @return Response
     */
    public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        // Populate the $errors object of the view. Normally this is done in the ViewServiceProvider,
        // but the ViewServiceProvider is executed before the session data is loaded
        // in the /Illuminate/Session/Middleware class.
        if ($this->app['session.store']->has('errors')) {
            $this->app['view']->share('errors', $this->app['session.store']->get('errors'));
        }

        return $this->httpKernel->handle($request);
    }

    /**
     * Terminates a request/response cycle.
     *
     * @param Request $request
     * @param Response $response
     */
    public function terminate(Request $request, Response $response)
    {
        $this->httpKernel->terminate($request, $response);
    }

    /**
     * Use a stacked client to include middlewares.
     *
     * @see Illuminate\Foundation\Application::getStackedClient()
     * @return \Stack\StackedHttpKernel
     */
    protected function getStackedClient()
    {
        $method = new \ReflectionMethod(get_class($this->app), 'getStackedClient');
        $method->setAccessible(true);

        return $method->invoke($this->app);
    }

}