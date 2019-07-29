<?php

use Codeception\Util\HttpCode;
use \Codeception\Example;

class CreatePatternCest
{

    public function _before(ApiTester $I)
    {
    }

    public function getPatternsList(ApiTester $I)
    {
        $I->wantToTest('Get all patterns');
        $I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
        $I->sendGET('/patterns', ['page' => 1, 'per_page' => 25]);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
    }

    /**
     * @dataProvider patternProvider
     * @param ApiTester $I
     * @param Example $example
     */
    public function createNewPatterns(ApiTester $I, Example $example)
    {
        $I->wantToTest('Create new pattern ' . $example['pattern']);
        $I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
        $I->sendPOST('/patterns/', ['pattern' => $example['pattern']]);
        $I->seeResponseCodeIs(HttpCode::CREATED);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['pattern' => $example['pattern']]);
    }

    /**
     * @dataProvider patternIdProvider
     * @param ApiTester $I
     * @param Example $example
     */
    public function getPatternById(ApiTester $I, Example $example)
    {
        $I->wantToTest('Get pattern with ID ' . $example['pattern_id']);
        $I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
        $I->sendGET('/patterns/' . $example['pattern_id']);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['pattern_id' => $example['pattern_id']]);
    }

    /**
     * @dataProvider updatePatternProvider
     * @param ApiTester $I
     * @param Example $example
     */
    public function updatePatternById(ApiTester $I, Example $example)
    {
        $I->wantToTest('Update pattern with ID ' . $example['pattern_id']);
        $I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
        $I->sendPUT('/patterns/' . $example['pattern_id'], ['pattern' => $example['pattern']]);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['pattern_id' => $example['pattern_id'], 'pattern' => $example['pattern']]);
    }

    /**
     * @dataProvider patternIdProvider
     * @param ApiTester $I
     * @param Example $example
     */
    public function deletePatternById(ApiTester $I, Example $example)
    {
        $I->wantToTest('Delete pattern with ID ' . $example['pattern_id']);
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
        return [
            ['pattern_id' => 4448],
            ['pattern_id' => 4449],
            ['pattern_id' => 4450],
            ['pattern_id' => 4451]
        ];
    }

    /**
     * @return array
     */
    private function updatePatternProvider(): array
    {
        return [
            ['pattern_id' => 4448, 'pattern' => '.dsfdsfds4'],
            ['pattern_id' => 4449, 'pattern' => 'dsffds4dsd7'],
            ['pattern_id' => 4450, 'pattern' => 'd4sfs7df4'],
            ['pattern_id' => 4451, 'pattern' => '.fghg4df4gdf7']
        ];
    }
}
