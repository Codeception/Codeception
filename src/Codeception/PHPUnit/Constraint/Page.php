<?php
namespace Codeception\PHPUnit\Constraint;

use Codeception\Lib\Console\Message;

class Page extends \PHPUnit_Framework_Constraint_StringContains
{
    protected $uri;

    public function __construct($string, $uri = '')
    {
        $this->string = (string)$string;
        $this->uri = $uri;
        $this->ignoreCase = true;
    }

    protected function failureDescription($other)
    {
        $page = substr($other, 0, 300);
        $message = new Message($page);
        $message->style('info');
        $message->prepend("\n--> ");
        $message->prepend($this->uriMessage());
        if (strlen($other) > 300) {
            $debugMessage = new Message("[Content too long to display. See complete response in '_output' directory]");
            $debugMessage->style('debug')->prepend("\n");
            $message->append($debugMessage);
        }
        $message->append("\n--> ");
        return $message->getMessage() . $this->toString();
    }

    protected function uriMessage($onPage = "")
    {
        if (!$this->uri) {
            return "";
        }
        $message = new Message($this->uri);
        $message->style('bold');
        $message->prepend(" $onPage ");
        return $message;
    }
}
