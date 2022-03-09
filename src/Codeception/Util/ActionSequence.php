<?php

declare(strict_types=1);

namespace Codeception\Util;

use Closure;
use Codeception\Step\Action;
use Exception;

use function call_user_func_array;
use function codecept_debug;
use function get_class;
use function implode;
use function is_array;
use function str_replace;

/**
 * Class for defining an array actions to be executed inside `performOn` of WebDriver
 *
 * ```php
 * <?php
 * (new ActionSequence)->click('do')->click('undo');
 * ActionSequence::build()->click('do')->click('undo');
 * ```
 *
 * @method $this see($text, $selector = null)
 * @method $this dontSee($text, $selector = null)
 * @method $this seeElement($selector, $attributes = [])
 * @method $this dontSeeElement($selector, $attributes = [])
 * @method $this click($link, $context = null)
 * @method $this wait($timeout)
 * @method $this waitForElementChange($element, Closure $callback, $timeout = 30)
 * @method $this waitForElement($element, $timeout = 10)
 * @method $this waitForElementVisible($element, $timeout = 10)
 * @method $this waitForElementNotVisible($element, $timeout = 10)
 * @method $this waitForText($text, $timeout = 10, $selector = null)
 * @method $this submitForm($selector, array $params, $button = null)
 * @method $this seeLink($text, $url = null)
 * @method $this dontSeeLink($text, $url = null)
 * @method $this seeCheckboxIsChecked($checkbox)
 * @method $this dontSeeCheckboxIsChecked($checkbox)
 * @method $this seeInField($field, $value)
 * @method $this dontSeeInField($field, $value)
 * @method $this seeInFormFields($formSelector, array $params)
 * @method $this dontSeeInFormFields($formSelector, array $params)
 * @method $this selectOption($select, $option)
 * @method $this checkOption($option)
 * @method $this uncheckOption($option)
 * @method $this fillField($field, $value)
 * @method $this attachFile($field, $filename)
 * @method $this seeNumberOfElements($selector, $expected)
 * @method $this seeOptionIsSelected($selector, $optionText)
 * @method $this dontSeeOptionIsSelected($selector, $optionText)
 */
class ActionSequence
{
    /**
     * @var Action[]
     */
    protected array $actions = [];

    /**
     * Creates an instance
     */
    public static function build(): self
    {
        return new self();
    }

    public function __call(string $action, array $arguments): self
    {
        $this->addAction($action, $arguments);
        return $this;
    }

    protected function addAction(string $action, $arguments): void
    {
        if (!is_array($arguments)) {
            $arguments = [$arguments];
        }
        $this->actions[] = new Action($action, $arguments);
    }

    /**
     * Creates action sequence from associative array,
     * where key is action, and value is action arguments
     */
    public function fromArray(array $actions): self
    {
        foreach ($actions as $action => $arguments) {
            $this->addAction($action, $arguments);
        }
        return $this;
    }

    /**
     * Returns a list of logged actions as associative array
     *
     * @return Action[]
     */
    public function getActions(): array
    {
        return $this->actions;
    }

    /**
     * Executes sequence of action as methods of passed object.
     */
    public function run(object $context): void
    {
        foreach ($this->actions as $step) {
            codecept_debug("- {$step}");
            try {
                call_user_func_array([$context, $step->getAction()], $step->getArguments());
            } catch (Exception $exception) {
                $class = get_class($exception); // rethrow exception for a specific action
                throw new $class($exception->getMessage() . "\nat {$step}");
            }
        }
    }

    public function __toString(): string
    {
        $actionsLog = [];

        foreach ($this->actions as $step) {
            $args = str_replace('"', "'", $step->getArgumentsAsString(20));
            $actionsLog[] = $step->getAction() . ": {$args}";
        }

        return implode(', ', $actionsLog);
    }
}
