<?php


namespace API;


use AppConfig\Config;
use DB\DbWord;
use Log\LoggerInterface;

class ApiRequest
{

    private $logger;
    private $config;
    private $dbWord;

    public function __construct(LoggerInterface $logger, Config $config, DbWord $dbWord)
    {
        $this->logger = $logger;
        $this->config = $config;
        $this->dbWord = $dbWord;
    }

    public function processPathInfo(string $pathInfo): void
    {
        if (preg_match('/^[a-zA-Z]+$/', $pathInfo) == 1) {
            $hyphenatedWord = $this->dbWord->getHyphenatedWordFromDb($pathInfo);
            if (!empty($hyphenatedWord)) {
                $this->sendResponse(json_encode(array('word' => $pathInfo, 'hyphenatedWord' => $hyphenatedWord)));
            } else {
                $this->sendErrorJson("Word '$pathInfo' not exist!", 404);
            }
        }
    }

    public function getHyphenatedWordsList(): void
    {
        if (!empty($_GET['page']) && !empty($_GET['rowsInPage'])) {
            $hyphenatedWordsList = $this->dbWord->getHyphenatedWordsListFromDb($_GET['page'], $_GET['rowsInPage']);
            if ($hyphenatedWordsList === null) {
                $this->sendErrorJson('Cannot get list from database!', 500);
            } else $this->sendResponse(json_encode(array(
                'page' => $_GET['page'],
                'rowsInPage' => $_GET['rowsInPage'],
                'rows' => $hyphenatedWordsList
            )));
        } else $this->sendErrorJson("Please give required GET query fields 'page' and 'rowsInPage'.");
    }

    private function sendErrorJson(string $error, int $httpStatus = 400): void
    {
        $this->sendResponse(json_encode(array('error' => $error)), $httpStatus);
    }

    private function sendResponse(string $json, int $httpStatus = 200): void
    {
        http_response_code($httpStatus);
        header('Content-Type: text/json;charset=utf-8', true);
        echo $json;
    }

}