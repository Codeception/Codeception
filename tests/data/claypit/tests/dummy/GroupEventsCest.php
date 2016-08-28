<?php

class GroupEventsCest
{
	/**
     * @group countevents
     * @param DumbGuy $I
     */
	public function countGroupEvents(DumbGuy $I)
	{
		$I->wantTo('affirm that Group events fire only once');
	}
}