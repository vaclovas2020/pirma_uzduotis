<?php


namespace API;


use AppConfig\Config;
use DB\DbWord;
use Hyphenation\WordHyphenationTool;
use Log\LoggerInterface;
use SimpleCache\CacheInterface;


class WordsController implements ControllerInterface
{
    private $dbWord;
    private $hyphenationTool;
    private $response;

    public function __construct(LoggerInterface $logger, CacheInterface $cache, Config $config, ApiResponse $response)
    {
        $this->dbWord = new DbWord($config->getDbConfig());
        $this->hyphenationTool = new WordHyphenationTool($logger, $cache, $config);
        $this->response = $response;
    }

    public function printList(int $page, int $perPage): void
    {
        $pageCount = $this->dbWord->getHyphenatedWordsListPageCount($perPage);
        if ($page > $pageCount) {
            $this->response->sendErrorJson("Page number $page is higher than allowed $pageCount", 404);
            return;
        }
        $list = $this->dbWord->getHyphenatedWordsListFromDb($page, $perPage);
        $this->response->sendResponse(json_encode($list));
    }

    public function print(int $id): void
    {
        $pattern = $this->dbWord->getWordById($id);
        if (!empty($pattern)) {
            $this->response->sendResponse(json_encode($pattern));
        } else {
            $this->response->sendErrorJson("Pattern with ID $id not exist!", 404);
        }
    }

    public function add(array $data): void
    {
        if (!empty($data['word'])) {
            $word = $data['word'];
            if ($this->dbWord->isWordSavedToDb($word)) {
                $hyphenatedWord = $this->dbWord->getHyphenatedWordFromDb($word);
                $this->response->sendResponse(json_encode(array(
                        'word_id' => $this->dbWord->getWordId($word),
                        'word' => $word,
                        'hyphenated_word' => $hyphenatedWord)
                ), 409);
            } else {
                $hyphenatedWord = $this->hyphenationTool->hyphenateWord($word);
                $this->response->sendResponse(json_encode(array(
                        'word_id' => $this->dbWord->getWordId($word),
                        'word' => $word,
                        'hyphenated_word' => $hyphenatedWord)
                ), 201);
            }
        } else $this->response->sendErrorJson("Please give required POST query field 'word'.");
    }

    public function update(int $id, array $data): void
    {
        $word = $this->dbWord->getWordById($id);
        if (!empty($word)) {
            if (!empty($data['word']) && !empty($data['hyphenated_word'])) {
                $word = $data['word'];
                $hyphenatedWord = $data['hyphenated_word'];
                if (preg_match('/[a-zA-Z]+/', $word) === 1) {
                    if (preg_match('/[a-zA-Z-]+/', $hyphenatedWord) === 1) {
                        $success = $this->dbWord->updateWord($word, $hyphenatedWord, $id);
                        if (!$success) {
                            $this->response->sendErrorJson("Cannot update Word resource.", 500);
                            return;
                        }
                        $this->response->sendResponse(json_encode(array(
                            'word_id' => $id,
                            'word' => $word,
                            'hyphenated_word' => $hyphenatedWord
                        )));
                    } else $this->response->sendErrorJson("Field 'hyphenated_word' must have only these characters a-zA-Z and -");
                } else $this->response->sendErrorJson("Field 'word' must have only these characters a-zA-Z");
            } else $this->response->sendErrorJson("Please give required PUT query field 'word'.");
        } else {
            $this->response->sendErrorJson("Word with ID $id not exist!", 404);
        }
    }

    public function delete(int $id): void
    {
        if (!empty($this->dbWord->getWordById($id))) {
            if ($this->dbWord->deleteWord($id)) {
                $this->response->sendStatusCode(200);
            } else {
                $this->response->sendErrorJson("Cannot delete Word with ID $id from database!", 500);
            }
        } else {
            $this->response->sendErrorJson("Word with ID $id not exist!", 404);
        }
    }

}