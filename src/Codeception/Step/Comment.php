<?php
namespace Codeception\Step;

use Codeception\Lib\ModuleContainer;
use Codeception\Step as CodeceptionStep;

class Comment extends CodeceptionStep
{
    public function __toString()
    {
        return (string) $this->getAction();
    }

    public function toString($maxLength)
    {
        return mb_strcut($this->__toString(), 0, $maxLength, 'utf-8');
    }

    public function getHtml($highlightColor = '#732E81')
    {
        return '<strong>' . $this->getAction() . '</strong>';
    }

    public function getPhpCode($maxLength)
    {
        return '// ' . $this->getAction();
    }

    public function run(ModuleContainer $container = null)
    {
        // don't do anything, let's rest
    }

    public function getPrefix()
    {
        return '';
    }
}
