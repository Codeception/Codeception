<?php

namespace Codeception;

/**
 * This class is a metaclass for all Cest classes that you are using in your
 * functional or acceptance tests.
 */
class CestCase
{

	/**
	 *
	 * @param \Codeception\Event\Test $event
	 */
	public function _before($event)
	{
	}

	/**
	 *
	 * @param \Codeception\Event\Test $event
	 */
	public function _after($event)
	{
	}

	/**
	 *
	 * @param \Codeception\Event\Fail $event
	 */
	public function _failed($event)
	{
		$this->_after(new \Codeception\Event\Test($e->getTest()));
	}

}
