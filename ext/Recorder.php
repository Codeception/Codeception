<?php

namespace Codeception\Extension;

use Codeception\Event\StepEvent;
use Codeception\Event\SuiteEvent;
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
 * Saves a screenshot of each step in acceptance tests and shows them as a slideshow on one HTML page (here's an [example](https://codeception.com/images/recorder.gif))
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
 * * `ignore_steps` (default: []) - array of step names that should not be recorded (given the step passed), * wildcards supported. Meta steps can also be ignored.
 * * `success_color` (default: success) - bootstrap values to be used for color representation for passed tests
 * * `failure_color` (default: danger) - bootstrap values to be used for color representation for failed tests
 * * `error_color` (default: dark) - bootstrap values to be used for color representation for scenarios where there's an issue occurred while generating a recording
 * * `delete_orphaned` (default: false) - delete recording folders created via previous runs
 * * `include_microseconds` (default: false) - enable microsecond precision for recorded step time details
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
 * #### Skipping recording of steps with annotations
 *
 * It is also possible to skip recording of steps for specified tests by using the @skipRecording annotation.
 *
 * ```php
 * /**
 * * @skipRecording login
 * * @skipRecording amOnUrl
 * *\/
 * public function testLogin(AcceptanceTester $I)
 * {
 *     $I->login();
 *     $I->amOnUrl('https://codeception.com');
 * }
 * ```
 *
 */
class Recorder extends \Codeception\Extension
{
    /** @var array */
    protected $config = [
        'delete_successful'    => true,
        'module'               => 'WebDriver',
        'template'             => null,
        'animate_slides'       => true,
        'ignore_steps'         => [],
        'success_color'        => 'success',
        'failure_color'        => 'danger',
        'error_color'          => 'dark',
        'delete_orphaned'      => false,
        'include_microseconds' => false,
    ];

    /** @var string */
    protected $template = <<<EOF
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Recorder Result</title>

    <!-- Bootstrap Core CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" rel="stylesheet">

    <style>
        html,
        body {
            height: 100%;
        }
        .active {
            height: 100%;
        }
        .carousel-caption {
            background: rgba(0,0,0,0.8);
        }
        .carousel-caption.error {
            background: #c0392b !important;
        }
        .carousel-item {
            min-height: 100vh;
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
        .gradient-right {
             background:
                linear-gradient(to left, rgba(0,0,0,.4), rgba(0,0,0,.0))
        }
        .gradient-left {
            background:
                linear-gradient(to right, rgba(0,0,0,.4), rgba(0,0,0,.0))
        }
    </style>
</head>
<body>
    <!-- Navigation -->
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="navbar-header">
            <a class="navbar-brand" href="../records.html"></span>Recorded Tests</a>
        </div>
        <div class="collapse navbar-collapse" id="navbarText">
            <ul class="navbar-nav mr-auto">
                <span class="navbar-text">{{feature}}</span>
            </ul>
            <span class="navbar-text">{{test}}</span>
        </div>
    </nav>
    <header id="steps" class="carousel slide" data-ride="carousel">
        <!-- Indicators -->
        <ol class="carousel-indicators">
            {{indicators}}
        </ol>

        <!-- Wrapper for Slides -->
        <div class="carousel-inner">
            {{slides}}
        </div>

        <!-- Controls -->
        <a class="carousel-control-prev gradient-left" href="#steps" role="button" data-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="false"></span>
            <span class="sr-only">Previous</span>
        </a>
        <a class="carousel-control-next gradient-right" href="#steps" role="button" data-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="false"></span>
            <span class="sr-only">Next</span>
        </a>
    </header>

    <!-- jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>

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

    /** @var string */
    protected $indicatorTemplate = <<<EOF
<li data-target="#steps" data-slide-to="{{step}}" class="{{isActive}}"></li>
EOF;

    /** @var string */
    protected $indexTemplate = <<<EOF
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Recorder Results Index</title>

    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="navbar-header">
            <a class="navbar-brand" href="#">Recorded Tests
            </a>
        </div>
    </nav>
    <div class="container py-4">
        <h1>Record #{{seed}}</h1>
        <ul>
            {{records}}
        </ul>
    </div>
</body>

</html>

EOF;

    /** @var string */
    protected $slidesTemplate = <<<EOF
<div class="carousel-item {{isActive}}">
    <img class="mx-auto d-block mh-100" src="{{image}}">
    <div class="carousel-caption {{isError}}">
        <h5>{{caption}}</h5>
        <p>Step finished at <span style="color: #3498db">"{{timeStamp}}"</span></p>
    </div>
</div>
EOF;

    /** @var array */
    public static $events = [
        Events::SUITE_BEFORE => 'beforeSuite',
        Events::SUITE_AFTER  => 'afterSuite',
        Events::TEST_BEFORE  => 'before',
        Events::TEST_ERROR   => 'persist',
        Events::TEST_FAIL    => 'persist',
        Events::TEST_SUCCESS => 'cleanup',
        Events::STEP_AFTER   => 'afterStep',
    ];

    /** @var WebDriver */
    protected $webDriverModule;

    /** @var string */
    protected $dir;

    /** @var array */
    protected $slides = [];

    /** @var int */
    protected $stepNum = 0;

    /** @var string */
    protected $seed;

    /** @var array */
    protected $seeds;

    /** @var array */
    protected $recordedTests = [];

    /** @var array */
    protected $skipRecording = [];

    /** @var array */
    protected $errorMessages = [];

    /** @var bool */
    protected $colors;

    /** @var bool */
    protected $ansi;

    /** @var array */
    protected $timeStamps = [];

    /** @var string */
    private $dateFormat;

    private $isDisabled = false;

    public function beforeSuite(SuiteEvent $e)
    {
        $this->webDriverModule = null;

        if (!$e->getSuite()->count()) {
            $this->isDisabled = true;
            return; // skip for empty suites
        }

        if (!$this->hasModule($this->config['module'])) {
            $this->isDisabled = true;
            $this->writeln('Recorder is disabled, no available modules');
            return;
        }

        $this->seed = uniqid();
        $this->seeds[] = $this->seed;
        $this->webDriverModule = $this->getModule($this->config['module']);
        $this->skipRecording = [];
        $this->errorMessages = [];
        $this->dateFormat = $this->config['include_microseconds'] ? 'Y-m-d\TH:i:s.uP' : DATE_ATOM;
        $this->ansi = !isset($this->options['no-ansi']);
        $this->colors = !isset($this->options['no-colors']);

        if (!$this->webDriverModule instanceof ScreenshotSaver) {
            throw new ExtensionException(
                $this,
                'You should pass module which implements ' . ScreenshotSaver::class . ' interface'
            );
        }

        $this->writeln(
            sprintf(
                '⏺ <bold>Recording</bold> ⏺ step-by-step screenshots will be saved to <info>%s</info>',
                codecept_output_dir()
            )
        );
        $this->writeln("Directory Format: <debug>record_{$this->seed}_{filename}_{testname}</debug> ----");
    }

    public function afterSuite()
    {
        if ($this->isDisabled) {
            return;
        }

        $links = '';

        if (count($this->slides)) {
            foreach ($this->recordedTests as $suiteName => $suite) {
                $links .= "<ul><li><b>{$suiteName}</b></li><ul>";
                foreach ($suite as $fileName => $tests) {
                    $links .= "<li>{$fileName}</li><ul>";

                    foreach ($tests as $test) {
                        $links .= in_array($test['path'], $this->skipRecording, true)
                            ? "<li class=\"text{$this->config['error_color']}\">{$test['name']}</li>\n"
                            : '<li class="text-' . $this->config[$test['status'] . '_color']
                            . "\"><a href='{$test['index']}'>{$test['name']}</a></li>\n";
                    }

                    $links .= '</ul>';
                }
                $links .= '</ul></ul>';
            }

            $indexHTML = (new Template($this->indexTemplate))
                ->place('seed', $this->seed)
                ->place('records', $links)
                ->produce();

            try {
                file_put_contents(codecept_output_dir() . 'records.html', $indexHTML);
            } catch (\Exception $exception) {
                $this->writeln(
                    "⏺ An exception occurred while saving records.html: <info>{$exception->getMessage()}</info>"
                );
            }

            $this->writeln('⏺ Records saved into: <info>file://' . codecept_output_dir() . 'records.html</info>');
        }

        foreach ($this->errorMessages as $message) {
            $this->writeln($message);
        }
    }

    /**
     * @param TestEvent $e
     */
    public function before(TestEvent $e)
    {
        if ($this->isDisabled) {
            return;
        }
        $this->dir = null;
        $this->stepNum = 0;
        $this->slides = [];
        $this->timeStamps = [];

        $this->dir = codecept_output_dir() . "record_{$this->seed}_{$this->getTestName($e)}";
        $testPath = codecept_relative_path(Descriptor::getTestFullName($e->getTest()));

        try {
            !is_dir($this->dir) && !mkdir($this->dir) && !is_dir($this->dir);
        } catch (\Exception $exception) {
            $this->skipRecording[] = $testPath;
            $this->appendErrorMessage(
                $testPath,
                "⏺ An exception occurred while creating directory: <info>{$this->dir}</info>"
            );
        }
    }

    /**
     * @param TestEvent $e
     */
    public function cleanup(TestEvent $e)
    {
        if ($this->isDisabled) {
            return;
        }

        if ($this->config['delete_orphaned']) {
            $recordingDirectories = [];
            $directories = new \DirectoryIterator(codecept_output_dir());

            // getting a list of currently present recording directories
            foreach ($directories as $directory) {
                preg_match('/^record_(.*?)_[^\n]+.php_[^\n]+$/', $directory->getFilename(), $match);
                if (isset($match[1])) {
                    $recordingDirectories[$match[1]][] = codecept_output_dir() . $directory->getFilename();
                }
            }

            // removing orphaned recording directories
            foreach (array_diff(array_keys($recordingDirectories), $this->seeds) as $orphanedSeed) {
                foreach ($recordingDirectories[$orphanedSeed] as $orphanedDirectory) {
                    FileSystem::deleteDir($orphanedDirectory);
                }
            }
        }

        if (!$this->webDriverModule || !$this->dir) {
            return;
        }
        if (!$this->config['delete_successful']) {
            $this->persist($e);

            return;
        }

        // deleting successfully executed tests
        FileSystem::deleteDir($this->dir);
    }

    /**
     * @param TestEvent $e
     */
    public function persist(TestEvent $e)
    {
        if ($this->isDisabled) {
            return;
        }

        $indicatorHtml = '';
        $slideHtml = '';
        $testName = $this->getTestName($e);
        $testPath = codecept_relative_path(Descriptor::getTestFullName($e->getTest()));
        $dir = codecept_output_dir() . "record_{$this->seed}_$testName";
        $status = 'success';

        if (strcasecmp($this->dir, $dir) !== 0) {
            $filename = str_pad(0, 3, '0', STR_PAD_LEFT) . '.png';

            try {
                !is_dir($dir) && !mkdir($dir) && !is_dir($dir);
                $this->dir = $dir;
            } catch (\Exception $exception) {
                $this->skipRecording[] = $testPath;
                $this->appendErrorMessage(
                    $testPath,
                    "⏺ An exception occurred while creating directory: <info>{$dir}</info>"
                );
            }

            $this->slides = [];
            $this->timeStamps = [];
            $this->slides[$filename] = new Step\Action('encountered an unexpected error prior to the test execution');
            $this->timeStamps[$filename] = (new \DateTime())->format($this->dateFormat);
            $status = 'error';

            try {
                if ($this->webDriverModule->webDriver === null) {
                    throw new ExtensionException($this, 'Failed to save screenshot as webDriver is not set');
                }

                $this->webDriverModule->webDriver->takeScreenshot($this->dir . DIRECTORY_SEPARATOR . $filename);
            } catch (\Exception $exception) {
                $this->appendErrorMessage(
                    $testPath,
                    "⏺ Unable to capture a screenshot for <info>{$testPath}/before</info>"
                );
            }
        }

        if (!in_array($testPath, $this->skipRecording, true)) {
            foreach ($this->slides as $i => $step) {
                /** @var Step $step */
                if ($step->hasFailed()) {
                    $status = 'failure';
                }

                $indicatorHtml .= (new Template($this->indicatorTemplate))
                    ->place('step', (int)$i)
                    ->place('isActive', (int)$i ? '' : 'active')
                    ->produce();

                $slideHtml .= (new Template($this->slidesTemplate))
                    ->place('image', $i)
                    ->place('caption', $step->getHtml('#3498db'))
                    ->place('isActive', (int)$i ? '' : 'active')
                    ->place('isError', $status === 'success' ? '' : 'error')
                    ->place('timeStamp', $this->timeStamps[$i])
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
            $environment = $e->getTest()->getMetadata()->getCurrent('env') ?: '';
            $suite = ucfirst(basename(\dirname($e->getTest()->getMetadata()->getFilename())));
            $testName = basename($e->getTest()->getMetadata()->getFilename());

            try {
                file_put_contents($indexFile, $html);
            } catch (\Exception $exception) {
                $this->skipRecording[] = $testPath;
                $this->appendErrorMessage(
                    $testPath,
                    "⏺ An exception occurred while saving index.html for <info>{$testPath}: "
                    . "{$exception->getMessage()}</info>"
                );
            }

            $this->recordedTests["{$suite} ({$environment})"][$testName][] = [
                'name' => $e->getTest()->getMetadata()->getName(),
                'path' => $testPath,
                'status' => $status,
                'index' => substr($indexFile, strlen(codecept_output_dir())),
            ];
        }
    }

    /**
     * @param StepEvent $e
     */
    public function afterStep(StepEvent $e)
    {
        if ($this->isDisabled) {
            return;
        }

        if ($this->dir === null) {
            return;
        }

        if ($e->getStep() instanceof CommentStep) {
            return;
        }

        // only taking the ignore step into consideration if that step has passed
        if ($this->isStepIgnored($e) && !$e->getStep()->hasFailed()) {
            return;
        }

        $filename = str_pad($this->stepNum, 3, '0', STR_PAD_LEFT) . '.png';

        try {
            if ($this->webDriverModule->webDriver === null) {
                throw new ExtensionException($this, 'Failed to save screenshot as webDriver is not set');
            }

            $this->webDriverModule->webDriver->takeScreenshot($this->dir . DIRECTORY_SEPARATOR . $filename);
        } catch (\Exception $exception) {
            $testPath = codecept_relative_path(Descriptor::getTestFullName($e->getTest()));
            $this->appendErrorMessage(
                $testPath,
                "⏺ Unable to capture a screenshot for <info>{$testPath}/{$e->getStep()->getAction()}</info>"
            );
        }

        $this->stepNum++;
        $this->slides[$filename] = $e->getStep();
        $this->timeStamps[$filename] = (new \DateTime())->format($this->dateFormat);
    }

    /**
     * @param StepEvent $e
     *
     * @return bool
     */
    protected function isStepIgnored(StepEvent $e)
    {
        $configIgnoredSteps = $this->config['ignore_steps'];
        $annotationIgnoredSteps = $e->getTest()->getMetadata()->getParam('skipRecording');

        $ignoredSteps = array_unique(
            array_merge(
                $configIgnoredSteps,
                is_array($annotationIgnoredSteps) ? $annotationIgnoredSteps : []
            )
        );

        foreach ($ignoredSteps as $stepPattern) {
            $stepRegexp = '/^' . str_replace('*', '.*?', $stepPattern) . '$/i';

            if (preg_match($stepRegexp, $e->getStep()->getAction())) {
                return true;
            }

            if ($e->getStep()->getMetaStep() !== null &&
                preg_match($stepRegexp, $e->getStep()->getMetaStep()->getAction())
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param StepEvent|TestEvent $e
     *
     * @return string
     */
    private function getTestName($e)
    {
        return basename($e->getTest()->getMetadata()->getFilename()) . '_' . preg_replace('/[^A-Za-z0-9\-\_]/', '_', $e->getTest()->getMetadata()->getName());
    }

    /**
     * @param string $message
     */
    protected function writeln($message)
    {
        parent::writeln(
            $this->ansi
            ? $message
            : trim(preg_replace('/[ ]{2,}/', ' ', str_replace('⏺', '', $message)))
        );
    }

    /**
     * @param string $testPath
     * @param string $message
     */
    private function appendErrorMessage($testPath, $message)
    {
        $this->errorMessages[$testPath] = array_merge(
            array_key_exists($testPath, $this->errorMessages) ? $this->errorMessages[$testPath]: [],
            [$message]
        );
    }
}
