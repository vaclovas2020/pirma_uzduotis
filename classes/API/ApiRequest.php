<?php


namespace API;


use AppConfig\Config;
use DB\DbWord;
use Hyphenation\WordHyphenationTool;
use Log\LoggerInterface;
use SimpleCache\CacheInterface;

class ApiRequest
{

    private $logger;
    private $config;
    private $cache;
    private $dbWord;
    private $hyphenationTool;

    public function __construct(LoggerInterface $logger, Config $config, DbWord $dbWord, CacheInterface $cache)
    {
        $this->logger = $logger;
        $this->config = $config;
        $this->dbWord = $dbWord;
        $this->cache = $cache;
        $this->hyphenationTool = new WordHyphenationTool($this->logger, $this->cache, $this->config);
    }

    public function processRequest(string $resource, string $method): void
    {
        if (!empty($_SERVER['PATH_INFO'])) {
            $this->processPathInfo($resource, substr($_SERVER['PATH_INFO'], 1), $method);
        } else if ($method === 'GET') {
            $this->printResources($resource);
        } else {
            $this->sendErrorJson('Method Not Allowed. Please use GET, PUT or DELETE', 405);
        }
    }

    private function processPathInfo(string $resource, string $name, string $method): void
    {
        if (preg_match('/^[a-zA-Z0-9]+$/', $name) == 1) {
            switch ($method) {
                case 'GET':
                    if ($resource === 'word') {
                        $this->printWord($name);
                    }
                    break;
                case 'PUT':
                    if ($resource === 'word') {
                        $this->addWord($name);
                    }
                    break;
                case 'DELETE':
                    if ($resource === 'word') {
                        $this->deleteWord($name);
                    }
                    break;
                default:
                    $this->sendErrorJson('Method Not Allowed. Please use GET, PUT or DELETE', 405);
            }
        } else {
            $this->sendErrorJson('Allowed resource name pattern is ^[a-zA-Z0-9]+', 400);
        }
    }

    private function printWord(string $word): void
    {
        $hyphenatedWord = $this->dbWord->getHyphenatedWordFromDb($word);
        if (!empty($hyphenatedWord)) {
            $this->sendResponse(json_encode(array('word' => $word, 'hyphenatedWord' => $hyphenatedWord)));
        } else {
            $this->sendErrorJson("Word '$word' not exist!", 404);
        }
    }

    private function addWord($word): void
    {
        if ($this->dbWord->isWordSavedToDb($word)) {
            $hyphenatedWord = $this->dbWord->getHyphenatedWordFromDb($word);
            $this->sendResponse(json_encode(array('word' => $word, 'hyphenatedWord' => $hyphenatedWord)), 409);
        } else {
            $hyphenatedWord = $this->hyphenationTool->hyphenateWord($word);
            $this->sendResponse(json_encode(array('word' => $word, 'hyphenatedWord' => $hyphenatedWord)), 201);
        }
    }

    private function deleteWord($word): void
    {
        if ($this->dbWord->isWordSavedToDb($word)) {
            if ($this->dbWord->deleteWord($word)){
                $this->sendSuccessJson("Word '$word' deleted!", 200);
            }
            else{
                $this->sendErrorJson("Cannot delete word '$word' from database!", 500);
            }
        } else {
            $this->sendErrorJson("Word '$word' not exist!", 404);
        }
    }

    private function printResources(string $resource): void
    {
        if (!empty($_GET['page']) && !empty($_GET['rowsInPage'])) {
            $page = $_GET['page'];
            $rowsInPage = $_GET['rowsInPage'];
            $pageCount = $this->getResourcesPageCount($resource, $rowsInPage);
            if ($_GET['page'] > $pageCount) {
                $this->sendErrorJson("Page number {$_GET['page']} not found! Last page number is $pageCount.", 404);
            } else {
                $list = $this->getResourcesList($resource, $page, $rowsInPage);
                if ($list === null) {
                    $this->sendErrorJson('Cannot get list from database!', 500);
                } else $this->sendResponse(json_encode(array(
                    'currentPage' => $_GET['page'],
                    'lastPage' => $pageCount,
                    'rowsInPage' => $_GET['rowsInPage'],
                    'rows' => $list
                )));
            }
        } else $this->sendErrorJson("Please give required GET query fields 'page' and 'rowsInPage'.");
    }

    private function getResourcesPageCount(string $resource, int $rowsInPage): int
    {
        $pageCount = 0;
        switch ($resource) {
            case 'word':
                $pageCount = $this->dbWord->getHyphenatedWordsListPageCount($rowsInPage);
                break;
            default:
                break;
        }
        return $pageCount;
    }

    private function getResourcesList(string $resource, int $page, int $rowsInPage): array
    {
        $list = null;
        switch ($resource) {
            case 'word':
                $list = $this->dbWord->getHyphenatedWordsListFromDb($page, $rowsInPage);
                break;
            default:
                break;
        }
        return $list;
    }


    private function sendErrorJson(string $error, int $httpStatus = 400): void
    {
        $this->sendResponse(json_encode(array('error' => $error)), $httpStatus);
    }

    private function sendSuccessJson(string $success, int $httpStatus = 400): void
    {
        $this->sendResponse(json_encode(array('success' => $success)), $httpStatus);
    }

    private function sendResponse(string $json, int $httpStatus = 200): void
    {
        http_response_code($httpStatus);
        header('Content-Type: text/json;charset=utf-8', true);
        echo $json;
    }

}