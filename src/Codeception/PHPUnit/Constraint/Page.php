<?php
namespace Codeception\PHPUnit\Constraint;

use Codeception\Util\Console\Message;

class Page extends \PHPUnit_Framework_Constraint_StringContains
{
    protected $uri;

    public function __construct($string, $uri = '')
    {
        $this->string     = (string)$string;
        $this->uri = $uri;
        $this->ignoreCase = true;
    }

    protected function failureDescription($other)
    {
        $page = substr($other,0,300);
        $message = new Message($page);
        $message->style('info');
        if ($this->uri) {
            $uriMessage = new Message($this->uri);
            $uriMessage->style('bold')->prepend(' Page ');
            $message->prepend($uriMessage);
        }
        if (strlen($other) > 300) {
            $debugMessage = new Message("[Content too long to display. See complete response in '_log' directory]");
            $debugMessage->style('debug')->prepend("\n");
            $message->append($debugMessage);
        }
        $message->prepend("\n-->")->append("\n--> ");
        return $message->getMessage() . $this->toString();
    }

}
