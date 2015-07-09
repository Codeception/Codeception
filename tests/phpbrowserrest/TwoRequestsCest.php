<?php
class TwoRequestsRestCest
{
    public function testFirstRequest(RestTester $I)
    {
        $I->wantTo('make the first request');
        $I->sendGET('http://localhost:8010/rest/user/');
    }
    
    /**
     * @after testFirstRequest
     */
    public function testSecondRequest(RestTester $I)
    {
        $I->wantTo('make the second request');
        $I->sendGET('http://localhost:8010/rest/user/');
    }
}
