<?php

class UseTypedHelperCest
{
    public function executeActions(UnitTester $I)
    {
        $I->comment('print comment');
        $I->getInt();
        $I->getDomDocument();
        $I->seeSomething();
        $I->getUnion();
        $I->getIntersection();
        $I->getSelf();
        $I->getParent();
    }
}
