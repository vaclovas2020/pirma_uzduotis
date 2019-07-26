<?php

use Codeception\Util\HttpCode;

class CreatePatternCest
{
    public function _before(ApiTester $I)
    {

    }

    // tests
    public function tryToTest(ApiTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
        $I->sendPOST('/patterns/', ['pattern' => '.test']);
        $I->seeResponseCodeIs(HttpCode::CREATED); // 200
        $I->seeResponseIsJson();
    }
}
