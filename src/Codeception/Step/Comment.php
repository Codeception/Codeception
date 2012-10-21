<?php
namespace Codeception\Step;
 
class Comment extends \Codeception\Step {

	public function getName() {
	    return 'Comment';
	}
    
	public function __toString() {
	    return $this->getAction();
	}

    public function getHtmlAction() {
        return '<strong>' . $this->getAction(). '</strong>';
    }

}
