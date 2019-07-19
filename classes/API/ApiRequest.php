<?php


namespace API;


use AppConfig\Config;
use DB\DbPatterns;
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
    private $dbPatterns;
    private $hyphenationTool;

    public function __construct(LoggerInterface $logger, Config $config, CacheInterface $cache)
    {
        $this->logger = $logger;
        $this->config = $config;
        $this->dbWord = new DbWord($config->getDbConfig());
        $this->dbPatterns = new DbPatterns($config, $logger, $cache);
        $this->cache = $cache;
        $this->hyphenationTool = new WordHyphenationTool($this->logger, $this->cache, $this->config);
    }

    public function processRequest(string $resource, string $method): void
    {
        $path = substr($_SERVER['PATH_INFO'], 1);
        $id = $this->filterPath($path, $method !== 'POST' && !empty($path));
        switch ($method) {
            case 'GET':
                if (empty($path)) {
                    $this->printResources($resource);
                } else if (!empty($id)) {
                    $this->printResource($resource, $id);
                }
                break;
            case
            'POST':
                $this->addResource($resource);
                break;
            case 'DELETE':
                if (!empty($id)) {
                    $this->deleteResource($resource, $id);
                }
                break;
            default:
                $this->sendErrorJson('Method Not Allowed. Please use GET, POST or DELETE', 405);
        }
    }

    private function filterPath(string $path, bool $returnErrorJson = false): string
    {
        if (preg_match('/^[0-9]+$/', $path) == 1) {
            return $path;
        } else if ($returnErrorJson) {
            $this->sendErrorJson('Resource ID must be number');
        }
        return '';
    }

    private function printResource(string $resource, int $id): void
    {
        switch ($resource) {
            case 'word':
                $this->printWord($id);
                break;
            case 'pattern':
                $this->printPattern($id);
                break;
            default:
                $this->sendErrorJson('Unknown resource!', 404);
                break;
        }
    }

    private function deleteResource(string $resource, int $id): void
    {
        switch ($resource) {
            case 'word':
                $this->deleteWord($id);
                break;
            case 'pattern':
                $this->deletePattern($id);
                break;
            default:
                $this->sendErrorJson('Unknown resource!', 404);
                break;
        }
    }

    private function addResource(string $resource): void
    {
        switch ($resource) {
            case 'word':
                $this->addWord();
                break;
            case 'pattern':
                $this->addPattern();
                break;
            default:
                $this->sendErrorJson('Unknown resource!', 404);
                break;
        }
    }

    private function printWord(int $id): void
    {
        $word = $this->dbWord->getWordById($id);
        if (!empty($word)) {
            $this->sendResponse(json_encode($word));
        } else {
            $this->sendErrorJson("Word with ID $id not exist!", 404);
        }
    }

    private function printPattern(int $id): void
    {
        $pattern = $this->dbPatterns->getPattern($id);
        if (!empty($pattern)) {
            $this->sendResponse(json_encode($pattern));
        } else {
            $this->sendErrorJson("Pattern with ID $id not exist!", 404);
        }
    }

    private function addWord(): void
    {
        if (!empty($_POST['word'])) {
            $word = $_POST['word'];
            if ($this->dbWord->isWordSavedToDb($word)) {
                $hyphenatedWord = $this->dbWord->getHyphenatedWordFromDb($word);
                $this->sendResponse(json_encode(array(
                        'word_id' => $this->dbWord->getWordId($word),
                        'word' => $word,
                        'hyphenated_word' => $hyphenatedWord)
                ), 409);
            } else {
                $hyphenatedWord = $this->hyphenationTool->hyphenateWord($word);
                $this->sendResponse(json_encode(array(
                        'word_id' => $this->dbWord->getWordId($word),
                        'word' => $word,
                        'hyphenated_word' => $hyphenatedWord)
                ), 201);
            }
        } else $this->sendErrorJson("Please give required POST query field 'word'.");
    }

    private function addPattern(): void
    {
        if (!empty($_POST['pattern'])) {
            $pattern = $_POST['pattern'];
            if (preg_match('/[a-z0-9.]+/', $pattern) === 1) {
                $patternId = $this->dbPatterns->getPatternIdByPatternStr($pattern);
                $created = false;
                if ($patternId === -1) {
                    $created = true;
                    $patternId = $this->dbPatterns->addPattern($pattern);
                    if ($patternId === -1) {
                        $this->sendErrorJson("Cannot create Pattern resource.", 500);
                        return;
                    }
                }
                $this->sendResponse(json_encode(array(
                    'pattern_id' => $patternId,
                    'pattern' => $pattern
                )), ($created) ? 201 : 409);
            } else $this->sendErrorJson("Field 'pattern' must have only these characters a-z0-9.");
        } else $this->sendErrorJson("Please give required POST query field 'pattern'.");
    }

    private function deleteWord(int $id): void
    {
        if (!empty($this->dbWord->getWordById($id))) {
            if ($this->dbWord->deleteWord($id)) {
                $this->sendSuccessJson("Word with ID $id deleted!", 200);
            } else {
                $this->sendErrorJson("Cannot delete Word with ID $id from database!", 500);
            }
        } else {
            $this->sendErrorJson("Word with ID $id not exist!", 404);
        }
    }

    private function deletePattern(int $id): void
    {
        if (!empty($this->dbPatterns->getPattern($id))) {
            if ($this->dbPatterns->deletePattern($id)) {
                $this->sendSuccessJson("Pattern with ID $id deleted!", 200);
            } else {
                $this->sendErrorJson("Cannot delete Pattern with ID $id from database!", 500);
            }
        } else {
            $this->sendErrorJson("Pattern with ID $id not exist!", 404);
        }
    }

    private function printResources(string $resource): void
    {
        if (!empty($_GET['page']) && !empty($_GET['per_page'])) {
            $page = $_GET['page'];
            $perPage = $_GET['per_page'];
            if (preg_match('/^[0-9]+$/', $page) == 1 && preg_match('/^[0-9]+$/', $perPage) == 1) {
                $pageCount = $this->getResourcesPageCount($resource, $perPage);
                if ($page > $pageCount) {
                    $this->sendErrorJson("Page number $page not found! Last page number is $pageCount.", 404);
                } else {
                    $list = $this->getResourcesList($resource, $page, $perPage);
                    if ($list === null) {
                        $this->sendErrorJson('Cannot get list from database!', 500);
                    } else $this->sendResponse(json_encode($list));
                }
            } else $this->sendErrorJson("GET query fields 'page' and 'per_page' must be numbers.");
        } else $this->sendErrorJson("Please give required GET query fields 'page' and 'perPage'.");
    }

    private function getResourcesPageCount(string $resource, int $perPage): int
    {
        $pageCount = 0;
        switch ($resource) {
            case 'word':
                $pageCount = $this->dbWord->getHyphenatedWordsListPageCount($perPage);
                break;
            case 'pattern':
                $pageCount = $this->dbPatterns->getPatternsListPageCount($perPage);
                break;
            default:
                break;
        }
        return $pageCount;
    }

    private function getResourcesList(string $resource, int $page, int $perPage): array
    {
        $list = null;
        switch ($resource) {
            case 'word':
                $list = $this->dbWord->getHyphenatedWordsListFromDb($page, $perPage);
                break;
            case 'pattern':
                $list = $this->dbPatterns->getPatternsList($page, $perPage);
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