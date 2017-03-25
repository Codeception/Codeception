<?php
namespace Codeception\Util;

use Codeception\Step\Action;

/**
 * Class for defining an array actions to be executed inside `performOn` of WebDriver
 *
 * ```php
 * <?php
 * (new ActionSequence)->click('do')->click('undo');
 * ActionSequence::build()->click('do')->click('undo');
 * ```
 *
 * @method $this see([optional])
 * @method $this dontSee([optional])
 * @method $this seeElement([optional])
 * @method $this dontSeeElement([optional])
 * @method $this click([optional])
 * @method $this wait([optional])
 * @method $this waitForElementChange([optional])
 * @method $this waitForElement([optional])
 * @method $this waitForElementVisible([optional])
 * @method $this waitForElementNotVisible([optional])
 * @method $this waitForText([optional])
 * @method $this submitForm([optional])
 * @method $this seeLink([optional])
 * @method $this dontSeeLink([optional])
 * @method $this seeCheckboxIsChecked([optional])
 * @method $this dontSeeCheckboxIsChecked([optional])
 * @method $this seeInField([optional])
 * @method $this dontSeeInField([optional])
 * @method $this seeInFormFields([optional])
 * @method $this dontSeeInFormFields([optional])
 * @method $this selectOption([optional])
 * @method $this checkOption([optional])
 * @method $this uncheckOption([optional])
 * @method $this fillField([optional])
 * @method $this attachFile([optional])
 * @method $this seeNumberOfElements([optional])
 * @method $this seeOptionIsSelected([optional])
 * @method $this dontSeeOptionIsSelected([optional])
 */
class ActionSequence
{
    protected $actions = [];

    /**
     * Creates an instance
     * @return ActionSequence
     */
    public static function build()
    {
        return new self;
    }

    public function __call($action, $arguments)
    {
        $this->addAction($action, $arguments);
        return $this;
    }

    protected function addAction($action, $arguments)
    {
        if (!is_array($arguments)) {
            $arguments = [$arguments];
        }
        $this->actions[] = new Action($action, $arguments);
    }

    /**
     * Creates action sequence from associative array,
     * where key is action, and value is action arguments
     *
     * @param array $actions
     * @return $this
     */
    public function fromArray(array $actions)
    {
        foreach ($actions as $action => $arguments) {
            $this->addAction($action, $arguments);
        }
        return $this;
    }

    /**
     * Returns a list of logged actions as associative array
     * @return array
     */
    public function toArray()
    {
        return $this->actions;
    }

    /**
     * Executes sequence of action as methods of passed object.
     *
     * @param $context
     */
    public function run($context)
    {
        foreach ($this->actions as $step) {
            /** @var $step Action  **/
            codecept_debug("- $step");
            try {
                call_user_func_array([$context, $step->getAction()], $step->getArguments());
            } catch (\Exception $e) {
                $class = get_class($e); // rethrow exception for a specific action
                throw new $class($e->getMessage() . "\nat $step");
            }
        }
    }

    public function __toString()
    {
        $actionsLog = [];

        foreach ($this->actions as $step) {
            $args = str_replace('"', "'", $step->getArgumentsAsString(20));
            $actionsLog[] = $step->getAction() . ": $args";
        }

        return implode(', ', $actionsLog);
    }
}
