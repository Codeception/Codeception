<?php
namespace Codeception\Step;
 
class Comment extends \Codeception\Step {

	public function getName() {
	    return 'Comment';
	}

	public function __toString() {
	    return "\n((I ".$this->humanize($this->getAction()).' '.$this->getArguments(true)."))";
	}

}
