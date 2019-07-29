<?php /** @noinspection PhpUnhandledExceptionInspection */


namespace API;


use AppConfig\Config;
use DB\DbWord;
use Exception\ApiException;
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
        $this->dbWord = new DbWord($config);
        $this->hyphenationTool = new WordHyphenationTool($logger, $cache, $config);
        $this->response = $response;
    }

    public function printList(int $page, int $perPage): void
    {
        $pageCount = $this->dbWord->getHyphenatedWordsListPageCount($perPage);
        if ($page > $pageCount) {
            throw new ApiException('Page number '.$page.' is higher than allowed $pageCount', 404);
        }
        $list = $this->dbWord->getHyphenatedWordsListFromDb($page, $perPage);
        $this->response->sendResponse(json_encode($list));
    }

    public function print(int $id): void
    {
        $pattern = $this->dbWord->getWordById($id);
        if (empty($pattern)) {
            throw new ApiException('Word with ID '.$id.' not exist!', 404);
        }
        $this->response->sendResponse(json_encode($pattern));
    }

    public function add(array $data): void
    {
        if (empty($data['word'])) {
            throw new ApiException('Please give required POST query field `word`.');
        }
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
    }

    public function update(int $id, array $data): void
    {
        $word = $this->dbWord->getWordById($id);
        if (empty($word)) {
            throw new ApiException('Word with ID '.$id.' not exist!', 404);
        }
        if (empty($data['word']) || empty($data['hyphenated_word'])) {
            throw new ApiException('Please give required PUT query fields `word` and `hyphenated_word`.');
        }
        $word = $data['word'];
        $hyphenatedWord = $data['hyphenated_word'];
        if (!Validator::validateValue('/[a-zA-Z]+/', $word)) {
            throw new ApiException('Field `word` must have only these characters a-zA-Z');
        }
        if (!Validator::validateValue('/[a-zA-Z]+/', $hyphenatedWord)) {
            throw new ApiException('Field `hyphenated_word` must have only these characters a-zA-Z and -');
        }
        if (!$this->dbWord->updateWord($word, $hyphenatedWord, $id)) {
            throw new ApiException('Cannot update Word resource.', 500);
        }
        $this->response->sendResponse(json_encode(array(
            'word_id' => $id,
            'word' => $word,
            'hyphenated_word' => $hyphenatedWord
        )));
    }

    public function delete(int $id): void
    {
        if (empty($this->dbWord->getWordById($id))) {
            throw new ApiException('Word with ID '.$id.' not exist!', 404);
        }
        if (!$this->dbWord->deleteWord($id)) {
            throw new ApiException('Cannot delete Word with ID '.$id.' from database!', 500);
        }
        $this->response->sendStatusCode(200);
    }

}