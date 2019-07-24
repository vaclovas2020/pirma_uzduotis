<?php /** @noinspection PhpUnhandledExceptionInspection */


namespace API;


use AppConfig\Config;
use DB\DbPatterns;
use Exception\ApiException;
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
            throw new ApiException("Page number $page is higher than allowed $pageCount", 404);
        }
        $list = $this->dbPatterns->getPatternsList($page, $perPage);
        $this->response->sendResponse(json_encode($list));
    }

    public function print(int $id): void
    {
        $pattern = $this->dbPatterns->getPattern($id);
        if (empty($pattern)) {
            throw new ApiException("Pattern with ID $id not exist!", 404);
        }
        $this->response->sendResponse(json_encode($pattern));
    }

    public function add(array $data): void
    {
        if (empty($data['pattern'])) {
            throw new ApiException("Please give required POST query field 'pattern'.");
        }
        $pattern = $data['pattern'];
        if (!Validator::validateValue('/[a-z0-9.]+/', $pattern)) {
            throw new ApiException("Field 'pattern' must have only these characters a-z0-9.");
        }
        $patternId = $this->dbPatterns->getPatternIdByPatternStr($pattern);
        $created = false;
        if ($patternId === -1) {
            $created = true;
            $patternId = $this->dbPatterns->addPattern($pattern);
            if ($patternId === -1) {
                throw new ApiException("Cannot create Pattern resource.", 500);
            }
        }
        $this->response->sendResponse(json_encode(array(
            'pattern_id' => $patternId,
            'pattern' => $pattern
        )), ($created) ? 201 : 409);

    }

    public function update(int $id, array $data): void
    {
        $patternStr = $this->dbPatterns->getPattern($id);
        if (empty($patternStr)) {
            throw new ApiException("Pattern with ID $id not exist!", 404);
        }
        if (empty($data['pattern'])) {
            throw new ApiException('Please give required PUT query field `pattern`.');
        }
        $pattern = $data['pattern'];
        if (!Validator::validateValue('/[a-z0-9.]+/', $pattern)) {
            throw new ApiException('Field `pattern` must have only these characters a-z0-9.');
        }
        if (!$this->dbPatterns->updatePattern($id, $pattern)) {
            throw new ApiException('Cannot update Pattern resource.', 500);
        }
        $this->response->sendResponse(json_encode(array(
            'pattern_id' => $id,
            'pattern' => $pattern
        )));
    }

    public function delete(int $id): void
    {
        if (empty($this->dbPatterns->getPattern($id))) {
            throw new ApiException("Pattern with ID $id not exist!", 404);
        }
        if (!$this->dbPatterns->deletePattern($id)) {
            throw new ApiException("Cannot delete Pattern with ID $id from database!", 500);
        }
        $this->response->sendStatusCode(200);
    }
}