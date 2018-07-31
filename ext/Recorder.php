<?php

namespace Codeception\Extension;

use Codeception\Event\StepEvent;
use Codeception\Event\TestEvent;
use Codeception\Events;
use Codeception\Exception\ExtensionException;
use Codeception\Lib\Interfaces\ScreenshotSaver;
use Codeception\Module\WebDriver;
use Codeception\Step;
use Codeception\Step\Comment as CommentStep;
use Codeception\Test\Descriptor;
use Codeception\Util\FileSystem;
use Codeception\Util\Template;

/**
 * Saves a screenshot of each step in acceptance tests and shows them as a slideshow on one HTML page (here's an [example](http://codeception.com/images/recorder.gif))
 * Activated only for suites with WebDriver module enabled.
 *
 * The screenshots are saved to `tests/_output/record_*` directories, open `index.html` to see them as a slideshow.
 *
 * #### Installation
 *
 * Add this to the list of enabled extensions in `codeception.yml` or `acceptance.suite.yml`:
 *
 * ``` yaml
 * extensions:
 *     enabled:
 *         - Codeception\Extension\Recorder
 * ```
 *
 * #### Configuration
 *
 * * `delete_successful` (default: true) - delete screenshots for successfully passed tests  (i.e. log only failed and errored tests).
 * * `module` (default: WebDriver) - which module for screenshots to use. Set `AngularJS` if you want to use it with AngularJS module. Generally, the module should implement `Codeception\Lib\Interfaces\ScreenshotSaver` interface.
 * * `ignore_steps` (default: []) - array of step names that should not be recorded, * wildcards supported
 *
 *
 * #### Examples:
 *
 * ``` yaml
 * extensions:
 *     enabled:
 *         - Codeception\Extension\Recorder:
 *             module: AngularJS # enable for Angular
 *             delete_successful: false # keep screenshots of successful tests
 *             ignore_steps: [have, grab*]
 * ```
 *
 */
class Recorder extends \Codeception\Extension
{
    protected $config = [
        'delete_successful' => true,
        'module'            => 'WebDriver',
        'template'          => null,
        'animate_slides'    => true,
        'ignore_steps'      => [],
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
    protected $errors = [];
    protected $errorMessages = [];

    public function beforeSuite()
    {
        $this->webDriverModule = null;
        if (!$this->hasModule($this->config['module'])) {
            $this->writeln("Recorder is disabled, no available modules");
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

        if (count($this->slides)) {
            foreach ($this->recordedTests as $link => $url) {
                $links .= "<li><a href='$url'>$link</a></li>\n";
            }
            $indexHTML = (new Template($this->indexTemplate))
                ->place('seed', $this->seed)
                ->place('records', $links)
                ->produce();

            file_put_contents(codecept_output_dir() . 'records.html', $indexHTML);
            $this->writeln("⏺ Records saved into: <info>file://" . codecept_output_dir() . 'records.html</info>');
        }

        foreach ($this->errors as $testPath => $screenshotPath) {
            while (count($this->errorMessages[$testPath])) {
                $this->writeln(array_pop($this->errorMessages[$testPath]));
            }

            if ($screenshotPath !== null) {
                $this->writeln("⏺ Screenshot saved into: <info>file://{$screenshotPath}</info>");
            }
        }
    }

    public function before(TestEvent $e)
    {
        if (!$this->webDriverModule) {
            return;
        }
        $this->dir = null;
        $this->stepNum = 0;
        $this->slides = [];
        $this->errors = [];
        $this->errorMessages = [];

        $testName = preg_replace('~\W~', '_', Descriptor::getTestAsString($e->getTest()));
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
        if (!$this->webDriverModule) {
            return;
        }
        $indicatorHtml = '';
        $slideHtml = '';

        $testName = preg_replace('~\W~', '_', Descriptor::getTestAsString($e->getTest()));
        $testPath = codecept_relative_path(Descriptor::getTestFullName($e->getTest()));
        $dir = codecept_output_dir() . "record_{$this->seed}_$testName";

        if ($this->dir !== $dir) {
            $screenshotPath = "{$dir}/error.png";
            @mkdir($dir);
            $this->errors = [];
            $this->recordedTests = [];
            $this->slides = [];
            $this->errorMessages[$testPath] = [
                "⏺ An error has occurred in <info>{$testName}</info> before any steps could've executed",
            ];

            try {
                $this->webDriverModule->webDriver->takeScreenshot($screenshotPath);
                $this->errors[$testPath] = $screenshotPath;
            } catch (\Exception $exception) {
                $this->errors[$testPath] = null;
                FileSystem::deleteDir($dir);
            }

            return;
        }

        if (!array_key_exists($testPath, $this->errors)) {
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
    }

    public function afterStep(StepEvent $e)
    {
        if (!$this->webDriverModule or !$this->dir) {
            return;
        }
        if ($e->getStep() instanceof CommentStep) {
            return;
        }
        if ($this->isStepIgnored($e->getStep())) {
            return;
        }

        $filename = str_pad($this->stepNum, 3, "0", STR_PAD_LEFT) . '.png';

        try {
            $this->webDriverModule->webDriver->takeScreenshot($this->dir . DIRECTORY_SEPARATOR . $filename);
        } catch (\Exception $exception) {
            $testPath = codecept_relative_path(Descriptor::getTestFullName($e->getTest()));
            $this->errors[$testPath] = null;

            if (array_key_exists($testPath, $this->errorMessages)) {
                $this->errorMessages[$testPath] = array_merge(
                    $this->errorMessages[$testPath],
                    ["⏺ Unable to capture a screenshot for <info>{$testPath}/{$e->getStep()->getAction()}</info>"]
                );
            } else {
                $this->errorMessages[$testPath] = [
                    "⏺ Unable to capture a screenshot for <info>{$testPath}/{$e->getStep()->getAction()}</info>",
                ];
            }

            return;
        }

        $this->stepNum++;
        $this->slides[$filename] = $e->getStep();
    }

    /**
     * @param Step $step
     * @return bool
     */
    protected function isStepIgnored($step)
    {
        foreach ($this->config['ignore_steps'] as $stepPattern) {
            $stepRegexp = '/^' . str_replace('*', '.*?', $stepPattern) . '$/i';
            if (preg_match($stepRegexp, $step->getAction())) {
                return true;
            }
        }

        return false;
    }
}
