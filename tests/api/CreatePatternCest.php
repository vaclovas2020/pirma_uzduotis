<?php

use Codeception\Util\HttpCode;

class CreatePatternCest
{
    private $createdObjectsId;

    public function _before(ApiTester $I)
    {
        $this->createdObjectsId = [];
    }

    public function getPatternsList(ApiTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
        $I->sendGET('/patterns', ['page' => 1, 'per_page' => 25]);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
    }

    /**
     * @dataProvider patternProvider
     */
    public function createNewPatterns(ApiTester $I, Example $example)
    {
        $I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
        $I->sendPOST('/patterns/', ['pattern' => $example['pattern']]);
        $I->seeResponseCodeIs(HttpCode::CREATED);
        $I->seeResponseIsJson();
        $json = $I->grabResponse();
        array_push($this->createdObjectsId, json_decode($json, true)['pattern_id']);
    }

    /**
     * @dataProvider patternIdProvider
     */
    public function getPatternById(ApiTester $I, Example $example)
    {
        $I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
        $I->sendGET('/patterns/' . $example['pattern_id']);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
    }

    /**
     * @dataProvider updatePatternProvider
     */
    public function updatePatternById(ApiTester $I, Example $example)
    {
        $I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
        $I->sendPUT('/patterns/' . $example['pattern_id'], ['pattern' => $example['pattern']]);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
    }

    /**
     * @dataProvider patternIdProvider
     */
    public function deletePatternById(ApiTester $I, Example $example)
    {
        $I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
        $I->sendDELETE('/patterns/' . $example['pattern_id']);
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    /**
     * @return array
     */
    private function patternProvider(): array
    {
        return [
            ['pattern' => ".test"],
            ['pattern' => "in4fo"],
            ['pattern' => ".a8bout"],
            ['pattern' => "con7tact"]
        ];
    }

    /**
     * @return array
     */
    private function patternIdProvider(): array
    {
        $patternsIdArray = [];
        foreach ($this->createdObjectsId as $id) {
            array_push($patternsIdArray, ['pattern_id' => $id]);
        }
        return $patternsIdArray;
    }

    /**
     * @return array
     */
    private function updatePatternProvider(): array
    {
        $patternsArray = [];
        foreach ($this->createdObjectsId as $id) {
            array_push($patternsArray, ['pattern_id' => $id, 'pattern' => 'a' . rand(2, 9) . 'c' . rand(2, 9)]);
        }
        return $patternsArray;
    }
}
