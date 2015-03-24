<?php
namespace Codeception\Lib\Connector;

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Request as DomRequest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Client;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;

class Laravel5 extends Client implements HttpKernelInterface, TerminableInterface
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
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->httpKernel = $this->app->make('Illuminate\Contracts\Http\Kernel');
        $this->httpKernel->bootstrap();
        $this->app->boot();

        parent::__construct($this);
    }

    /**
     * Handle a request.
     *
     * @param DomRequest $request
     * @param int $type
     * @param bool $catch
     * @return Response
     */
    public function handle(DomRequest $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        $request = Request::createFromBase($request);
        $request->enableHttpMethodParameterOverride();

        $this->app->bind('request', $request);

        return $this->httpKernel->handle($request);
    }

	/**
	 * Terminates a request/response cycle.
	 *
	 * @param DomRequest $request A Request instance
	 * @param Response $response A Response instance
	 *
	 * @api
	 */
	public function terminate(DomRequest $request, Response $response)
	{
		$this->httpKernel->terminate(Request::createFromBase($request), $response);
	}

}
