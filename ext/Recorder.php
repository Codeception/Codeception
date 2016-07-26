<?php
namespace Codeception\Extension;

use Codeception\Event\StepEvent;
use Codeception\Event\TestEvent;
use Codeception\Events;
use Codeception\Exception\ExtensionException;
use Codeception\Lib\Interfaces\ScreenshotSaver;
use Codeception\Module\WebDriver;
use Codeception\Step\Comment as CommentStep;
use Codeception\Test\Descriptor;
use Codeception\Util\FileSystem;
use Codeception\Util\Template;

/**
 * Saves screenshots of each step in acceptance tests and shows them as a slideshow.
 * Activated only for suites with WebDriver module enabled.
 *
 *  ![recorder](http://codeception.com/images/recorder.gif)
 *
 * Slideshows saves are saved into `tests/_output/record_*` directories.
 * Open `index.html` to see the slideshow.
 *
 * #### Installation
 *
 * Add to list of enabled extensions
 *
 * ``` yaml
 * extensions:
 *     enabled: [Codeception\Extension\Recorder]
 * ```
 *
 * #### Configuration
 *
 * * `delete_successful` (default: true) - delete records for successfully passed tests (log only failed and errored)
 * * `module` (default: WebDriver) - which module for screenshots to use.
 * Module should implement `Codeception\Lib\Interfaces\ScreenshotSaver` interface.
 * Currently only WebDriver or any its children can be used.
 *
 * ``` yaml
 * extensions:
 *     config:
 *         Codeception\Extension\Recorder:
 *             delete_successful: false
 * ```
 *
 */
class Recorder extends \Codeception\Extension
{
    protected $config = [
        'delete_successful' => true,
        'module'            => 'WebDriver',
        'template'          => null,
        'animate_slides'    => true
    ];

    protected $template = <<<EOF
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Recorder Result</title>

    <!-- Bootstrap Core CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet">

    <style>
        html,
        body {
            height: 100%;
        }
        .carousel,
        .item,
        .active {
            height: 100%;
        }
        .navbar {
            margin-bottom: 0px !important;
        }
        .carousel-caption {
            background: rgba(0,0,0,0.8);
            padding-bottom: 50px !important;
        }
        .carousel-caption.error {
            background: #c0392b !important;
        }

        .carousel-inner {
            height: 100%;
        }

        .fill {
            width: 100%;
            height: 100%;
            text-align: center;
            overflow-y: scroll;
            background-position: top;
            -webkit-background-size: cover;
            -moz-background-size: cover;
            background-size: cover;
            -o-background-size: cover;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-default" role="navigation">
        <div class="navbar-header">
            <a class="navbar-brand" href="#">{{feature}}
                <small>{{test}}</small>
            </a>
        </div>
    </nav>
    <header id="steps" class="carousel{{carousel_class}}">
        <!-- Indicators -->
        <ol class="carousel-indicators">
            {{indicators}}
        </ol>

        <!-- Wrapper for Slides -->
        <div class="carousel-inner">
            {{slides}}
        </div>

        <!-- Controls -->
        <a class="left carousel-control" href="#steps" data-slide="prev">
            <span class="icon-prev"></span>
        </a>
        <a class="right carousel-control" href="#steps" data-slide="next">
            <span class="icon-next"></span>
        </a>

    </header>

    <!-- jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>

    <!-- Script to Activate the Carousel -->
    <script>
    $('.carousel').carousel({
        wrap: true,
        interval: false
    })

    $(document).bind('keyup', function(e) {
      if(e.keyCode==39){
      jQuery('a.carousel-control.right').trigger('click');
      }

      else if(e.keyCode==37){
      jQuery('a.carousel-control.left').trigger('click');
      }

    });

    </script>

</body>

</html>
EOF;

    protected $indicatorTemplate = <<<EOF
<li data-target="#steps" data-slide-to="{{step}}" {{isActive}}></li>
EOF;

    protected $indexTemplate = <<<EOF
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Recorder Results Index</title>

    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-default" role="navigation">
        <div class="navbar-header">
            <a class="navbar-brand" href="#">Recorded Tests
            </a>
        </div>
    </nav>
    <div class="container">
        <h1>Record #{{seed}}</h1>
        <ul>
            {{records}}
        </ul>
    </div>

</body>

</html>

EOF;

    protected $slidesTemplate = <<<EOF
<div class="item {{isActive}}">
    <div class="fill">
        <img src="{{image}}">
    </div>
    <div class="carousel-caption {{isError}}">
        <h2>{{caption}}</h2>
        <small>scroll up and down to see the full page</small>
    </div>
</div>
EOF;

    public static $events = [
        Events::SUITE_BEFORE => 'beforeSuite',
        Events::SUITE_AFTER  => 'afterSuite',
        Events::TEST_BEFORE  => 'before',
        Events::TEST_ERROR   => 'persist',
        Events::TEST_FAIL    => 'persist',
        Events::TEST_SUCCESS => 'cleanup',
        Events::STEP_AFTER   => 'afterStep',
    ];

    /**
     * @var WebDriver
     */
    protected $webDriverModule;
    protected $dir;
    protected $slides = [];
    protected $stepNum = 0;
    protected $seed;
    protected $recordedTests = [];

    public function beforeSuite()
    {
        $this->webDriverModule = null;
        if (!$this->hasModule($this->config['module'])) {
            return;
        }
        $this->seed = uniqid();
        $this->webDriverModule = $this->getModule($this->config['module']);
        if (!$this->webDriverModule instanceof ScreenshotSaver) {
            throw new ExtensionException(
                $this,
                'You should pass module which implements Codeception\Lib\Interfaces\ScreenshotSaver interface'
            );
        }
        $this->writeln(sprintf(
            "⏺ <bold>Recording</bold> ⏺ step-by-step screenshots will be saved to <info>%s</info>",
            codecept_output_dir()
        ));
        $this->writeln("Directory Format: <debug>record_{$this->seed}_{testname}</debug> ----");
    }

    public function afterSuite()
    {
        if (!$this->webDriverModule or !$this->dir) {
            return;
        }
        $links = '';
        foreach ($this->recordedTests as $link => $url) {
            $links .= "<li><a href='$url'>$link</a></li>\n";
        }
        $indexHTML = (new Template($this->indexTemplate))
            ->place('seed', $this->seed)
            ->place('records', $links)
            ->produce();

        file_put_contents(codecept_output_dir().'records.html', $indexHTML);
        $this->writeln("⏺ Records saved into: <info>file://" . codecept_output_dir().'records.html</info>');
    }

    public function before(TestEvent $e)
    {
        if (!$this->webDriverModule) {
            return;
        }
        $this->dir = null;
        $this->stepNum = 0;
        $this->slides = [];
        $testName = preg_replace('~\W~', '.', Descriptor::getTestAsString($e->getTest()));
        $this->dir = codecept_output_dir() . "record_{$this->seed}_$testName";
        @mkdir($this->dir);
    }

    public function cleanup(TestEvent $e)
    {
        if (!$this->webDriverModule or !$this->dir) {
            return;
        }
        if (!$this->config['delete_successful']) {
            $this->persist($e);
            return;
        }

        // deleting successfully executed tests
        FileSystem::deleteDir($this->dir);
    }

    public function persist(TestEvent $e)
    {
        if (!$this->webDriverModule or !$this->dir) {
            return;
        }
        $indicatorHtml = '';
        $slideHtml = '';
        foreach ($this->slides as $i => $step) {
            $indicatorHtml .= (new Template($this->indicatorTemplate))
                ->place('step', (int)$i)
                ->place('isActive', (int)$i ? '' : 'class="active"')
                ->produce();

            $slideHtml .= (new Template($this->slidesTemplate))
                ->place('image', $i)
                ->place('caption', $step->getHtml('#3498db'))
                ->place('isActive', (int)$i ? '' : 'active')
                ->place('isError', $step->hasFailed() ? 'error' : '')
                ->produce();
        }

        $html = (new Template($this->template))
            ->place('indicators', $indicatorHtml)
            ->place('slides', $slideHtml)
            ->place('feature', ucfirst($e->getTest()->getFeature()))
            ->place('test', Descriptor::getTestSignature($e->getTest()))
            ->place('carousel_class', $this->config['animate_slides'] ? ' slide' : '')
            ->produce();

        $indexFile = $this->dir . DIRECTORY_SEPARATOR . 'index.html';
        file_put_contents($indexFile, $html);
        $testName = Descriptor::getTestSignature($e->getTest()). ' - '.ucfirst($e->getTest()->getFeature());
        $this->recordedTests[$testName] = substr($indexFile, strlen(codecept_output_dir()));
    }

    public function afterStep(StepEvent $e)
    {
        if (!$this->webDriverModule or !$this->dir) {
            return;
        }
        if ($e->getStep() instanceof CommentStep) {
            return;
        }

        $filename = str_pad($this->stepNum, 3, "0", STR_PAD_LEFT) . '.png';
        $this->webDriverModule->_saveScreenshot($this->dir . DIRECTORY_SEPARATOR . $filename);
        $this->stepNum++;
        $this->slides[$filename] = $e->getStep();
    }
}
