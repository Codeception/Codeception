<?php

declare(strict_types=1);

namespace Codeception;

use Codeception\Event\FailEvent;
use Codeception\Event\StepEvent;
use Codeception\Exception\ConditionalAssertionFailed;
use Codeception\Exception\InjectionException;
use Codeception\Step\Comment;
use Codeception\Step\Meta;
use Codeception\Test\Metadata;
use PHPUnit\Framework\IncompleteTestError;
use PHPUnit\Framework\SkippedTestError;
use PHPUnit\Framework\SkippedWithMessageException;
use PHPUnit\Runner\Version as PHPUnitVersion;

class Scenario
{
    protected Metadata $metadata;

    /** @var Step[] */
    protected array $steps = [];

    protected string $feature;

    protected ?Meta $metaStep = null;

    public function __construct(protected TestInterface $test)
    {
        $this->metadata = $this->test->getMetadata();
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

    public function current(?string $key = null)
    {
        return $this->metadata->getCurrent($key);
    }

    /**
     * @throws InjectionException
     */
    public function runStep(Step $step): mixed
    {
        $step->saveTrace();
        if ($this->metaStep instanceof Meta) {
            $step->setMetaStep($this->metaStep);
        }
        $this->steps[] = $step;

        $dispatcher = $this->metadata->getService('dispatcher');
        $dispatcher->dispatch(new StepEvent($this->test, $step), Events::STEP_BEFORE);

        try {
            $result = $step->run($this->metadata->getService('modules'));
        } catch (ConditionalAssertionFailed $failure) {
            $this->test->getResultAggregator()
                ->addFailure(new FailEvent(clone $this->test, $failure, 0));
            $result = null;
        } finally {
            $dispatcher->dispatch(new StepEvent($this->test, $step), Events::STEP_AFTER);
            $step->executed = true;
        }

        return $result;
    }

    public function addStep(Step $step): void
    {
        $this->steps[] = $step;
    }

    /** @return Step[] */
    public function getSteps(): array
    {
        return $this->steps;
    }

    public function getHtml(): string
    {
        $text = '';
        foreach ($this->steps as $step) {
            if ($step->getName() === 'Comment') {
                $text .= trim($step->getHumanizedArguments(), '"') . '<br/>';
            } else {
                $text .= $step->getHtml() . '<br/>';
            }
        }
        $text = str_replace(['"\'', '\'"'], ["'", "'"], $text);
        return '<h3>' . mb_strtoupper('I want to ' . $this->getFeature(), 'utf-8') . '</h3>' . $text;
    }

    public function getText(): string
    {
        $text = '';
        foreach ($this->steps as $step) {
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
        if (
            version_compare(PHPUnitVersion::series(), '10.0', '<')
            && class_exists(SkippedTestError::class)
        ) {
            throw new SkippedTestError($message);
        }
        throw new SkippedWithMessageException($message);
    }

    public function incomplete(string $message = ''): void
    {
        throw new IncompleteTestError($message);
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
