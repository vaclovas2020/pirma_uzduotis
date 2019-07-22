<?php


namespace API;


use AppConfig\Config;
use DB\DbPatterns;
use Log\LoggerInterface;
use SimpleCache\CacheInterface;

class PatternsController implements ControllerInterface
{
    private $response;
    private $dbPatterns;

    public function __construct(LoggerInterface $logger, CacheInterface $cache, Config $config, ApiResponse $response)
    {
        $this->dbPatterns = new DbPatterns($config, $logger, $cache);
        $this->response = $response;
    }

    public function printList(int $page, int $perPage): void
    {
        $pageCount = $this->dbPatterns->getPatternsListPageCount($perPage);
        if ($page > $pageCount) {
            $this->response->sendErrorJson("Page number $page is higher than allowed $pageCount", 404);
            return;
        }
        $list = $this->dbPatterns->getPatternsList($page, $perPage);
        $this->response->sendResponse(json_encode($list));
    }

    public function print(int $id): void
    {
        $pattern = $this->dbPatterns->getPattern($id);
        if (!empty($pattern)) {
            $this->response->sendResponse(json_encode($pattern));
        } else {
            $this->response->sendErrorJson("Pattern with ID $id not exist!", 404);
        }
    }

    public function add(array $data): void
    {
        if (!empty($data['pattern'])) {
            $pattern = $data['pattern'];
            if (preg_match('/[a-z0-9.]+/', $pattern) === 1) {
                $patternId = $this->dbPatterns->getPatternIdByPatternStr($pattern);
                $created = false;
                if ($patternId === -1) {
                    $created = true;
                    $patternId = $this->dbPatterns->addPattern($pattern);
                    if ($patternId === -1) {
                        $this->response->sendErrorJson("Cannot create Pattern resource.", 500);
                        return;
                    }
                }
                $this->response->sendResponse(json_encode(array(
                    'pattern_id' => $patternId,
                    'pattern' => $pattern
                )), ($created) ? 201 : 409);
            } else $this->response->sendErrorJson("Field 'pattern' must have only these characters a-z0-9.");
        } else $this->response->sendErrorJson("Please give required POST query field 'pattern'.");
    }

    public function update(int $id, array $data): void
    {
        $patternStr = $this->dbPatterns->getPattern($id);
        if (!empty($patternStr)) {
            if (!empty($data['pattern'])) {
                $pattern = $data['pattern'];
                if (preg_match('/[a-z0-9.]+/', $pattern) === 1) {
                    $success = $this->dbPatterns->updatePattern($id, $pattern);
                    if (!$success) {
                        $this->response->sendErrorJson("Cannot update Pattern resource.", 500);
                        return;
                    }
                    $this->response->sendResponse(json_encode(array(
                        'pattern_id' => $id,
                        'pattern' => $pattern
                    )));
                } else $this->response->sendErrorJson("Field 'pattern' must have only these characters a-z0-9.");
            } else $this->response->sendErrorJson("Please give required PUT query field 'pattern'.");
        } else {
            $this->response->sendErrorJson("Pattern with ID $id not exist!", 404);
        }
    }

    public function delete(int $id): void
    {
        if (!empty($this->dbPatterns->getPattern($id))) {
            if ($this->dbPatterns->deletePattern($id)) {
                $this->response->sendStatusCode(200);
            } else {
                $this->response->sendErrorJson("Cannot delete Pattern with ID $id from database!", 500);
            }
        } else {
            $this->response->sendErrorJson("Pattern with ID $id not exist!", 404);
        }
    }
}