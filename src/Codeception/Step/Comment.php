<?php
namespace Codeception\Step;
 
class Comment extends \Codeception\Step {

	public function __toString() {
	    return $this->getAction();
	}

    public function getHtmlAction() {
        return '<strong>' . $this->getAction(). '</strong>';
    }

    public function run()
    {
        // don't do anything, let's rest
    }

}
