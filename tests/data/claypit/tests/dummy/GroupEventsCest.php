<?php

class GroupEventsCest
{
	/**
	 * @group countevents
	 */
	public function countGroupEvents(DumbGuy $I)
	{
		$I->wantTo('affirm that Group events fire only once');
	}
}