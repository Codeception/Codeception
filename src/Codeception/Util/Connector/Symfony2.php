<?php
namespace Codeception\Util\Connector;

use Symfony\Component\Finder\Finder;

class Symfony2 extends \Symfony\Component\BrowserKit\Client
{
    protected $hasPerformedRequest = false;
    /**
     *
     * @api
     * @var \Symfony\Component\HttpKernel\Kernel
     */
    public $kernel;

    function __construct(\Symfony\Component\HttpKernel\Kernel $kernel) {
        $this->kernel = $kernel;
        parent::__construct();
    }

    public function doRequest($request) {

        $response = $this->kernel->handle($request);

        return $response;
    }

}
