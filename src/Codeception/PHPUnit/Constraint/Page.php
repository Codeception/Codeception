<?php
namespace Codeception\PHPUnit\Constraint;

use Codeception\Lib\Console\Message;

class Page extends \PHPUnit_Framework_Constraint
{
    protected $uri;

    public function __construct($string, $uri = '')
    {
        parent::__construct();
        $this->string = $this->normalizeText((string)$string);
        $this->uri = $uri;
    }

    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
     *
     * @param mixed $other Value or object to evaluate.
     *
     * @return bool
     */
    protected function matches($other)
    {
        $other = $this->normalizeText($other);
        return mb_stripos($other, $this->string, null, 'UTF-8') !== false;
    }

    /**
     * @param $text
     * @return string
     */
    private function normalizeText($text)
    {
        $text = strtr($text, "\r\n", "  ");
        return trim(preg_replace('/\\s{2,}/', ' ', $text));
    }

    /**
     * Returns a string representation of the constraint.
     *
     * @return string
     */
    public function toString()
    {
        $string = mb_strtolower($this->string, 'UTF-8');

        return sprintf(
            'contains "%s"',
            $string
        );
    }

    protected function failureDescription($other)
    {
        $page = substr($other, 0, 300);
        $message = new Message($page);
        $message->style('info');
        $message->prepend("\n--> ");
        $message->prepend($this->uriMessage());
        if (strlen($other) > 300) {
            $debugMessage = new Message(
                "[Content too long to display. See complete response in '" . codecept_output_dir() . "' directory]"
            );
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
