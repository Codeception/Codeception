<?php

declare(strict_types=1);

namespace Codeception;

use Codeception\Event\StepEvent;
use Codeception\Exception\ConditionalAssertionFailed;
use Codeception\Exception\InjectionException;
use Codeception\Step\Comment;
use Codeception\Step\Meta;
use Codeception\Test\Metadata;
use Exception;
use PHPUnit\Framework\IncompleteTestError;
use PHPUnit\Framework\SkippedTestError;

class Scenario
{
    protected TestInterface $test;

    protected Metadata $metadata;

    protected array $steps = [];

    protected string $feature;

    protected ?Meta $metaStep = null;

    public function __construct(TestInterface $test)
    {
        $this->metadata = $test->getMetadata();
        $this->test = $test;
    }

    public function setFeature(string $feature): void
    {
        $this->metadata->setFeature($feature);
    }

    public function getFeature(): string
    {
        return $this->metadata->getFeature();
    }

    public function getGroups(): array
    {
        return $this->metadata->getGroups();
    }

    public function current(?string $key)
    {
        return $this->metadata->getCurrent($key);
    }

    /**
     * @return mixed
     * @throws InjectionException
     */
    public function runStep(Step $step)
    {
        $step->saveTrace();
        if ($this->metaStep instanceof Meta) {
            $step->setMetaStep($this->metaStep);
        }
        $this->steps[] = $step;
        $result = null;
        $dispatcher = $this->metadata->getService('dispatcher');

        $dispatcher->dispatch(new StepEvent($this->test, $step), Events::STEP_BEFORE);
        try {
            $result = $step->run($this->metadata->getService('modules'));
        } catch (ConditionalAssertionFailed $failed) {
            $result = $this->test->getTestResultObject();
            if (is_null($result)) {
                $dispatcher->dispatch(new StepEvent($this->test, $step), Events::STEP_AFTER);
                throw $failed;
            } else {
                $result->addFailure(clone($this->test), $failed, $result->time());
            }
        } catch (Exception $exception) {
            $dispatcher->dispatch(new StepEvent($this->test, $step), Events::STEP_AFTER);
            throw $exception;
        }
        $dispatcher->dispatch(new StepEvent($this->test, $step), Events::STEP_AFTER);
        $step->executed = true;
        return $result;
    }

    public function addStep(Step $step): void
    {
        $this->steps[] = $step;
    }

    /**
     * Returns the steps of this scenario.
     */
    public function getSteps(): array
    {
        return $this->steps;
    }

    public function getHtml(): string
    {
        $text = '';
        foreach ($this->getSteps() as $step) {
            /** @var Step $step */
            if ($step->getName() !== 'Comment') {
                $text .= $step->getHtml() . '<br/>';
            } else {
                $text .= trim($step->getHumanizedArguments(), '"') . '<br/>';
            }
        }
        $text = str_replace(['"\'', '\'"'], ["'", "'"], $text);
        return "<h3>" . mb_strtoupper('I want to ' . $this->getFeature(), 'utf-8') . "</h3>" . $text;
    }

    public function getText(): string
    {
        $text = '';
        foreach ($this->getSteps() as $step) {
            $text .= $step->getPrefix() . "{$step} \r\n";
        }
        $text = trim(str_replace(['"\'', '\'"'], ["'", "'"], $text));
        return mb_strtoupper('I want to ' . $this->getFeature(), 'utf-8') . "\r\n\r\n" . $text . "\r\n\r\n";
    }

    public function comment(string $comment): void
    {
        $this->runStep(new Comment($comment, []));
    }

    public function skip(string $message = ''): void
    {
        throw new SkippedTestError($message);
    }

    public function incomplete(string $message = ''): void
    {
        throw new IncompleteTestError($message);
    }

    public function __call(string $method, array $args)
    {
        // all methods were deprecated and removed from here
        trigger_error(sprintf('Codeception: $scenario->%s() has been deprecated and removed. Use annotations to pass scenario params', $method), E_USER_DEPRECATED);
    }

    public function setMetaStep(?Meta $metaStep): void
    {
        $this->metaStep = $metaStep;
    }

    public function getMetaStep(): ?Meta
    {
        return $this->metaStep;
    }
}
