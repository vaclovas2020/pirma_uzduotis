<?php


use AppConfig\Config;
use AppConfig\DbConfig;
use DB\DbPatterns;
use Hyphenation\Pattern;
use Hyphenation\PatternDataLoader;
use IO\PatternFileIterator;
use Log\Logger;
use PHPUnit\Framework\TestCase;
use SimpleCache\FileCache;

class PatternTest extends TestCase
{

    private $dbPatterns;
    private $config;
    private $logger;
    private $cache;

    /**
     * @dataProvider provider
     * @param string $pattern
     */
    public function testGetPatternCharArray(string $pattern): void
    {
        $patternObj = new Pattern($this->config, $this->dbPatterns, $pattern);
        $matches = array();
        preg_match_all('/[0-9]+/', $pattern, $matches);
        $matchesCount = count($matches[0]);
        $patternChars = $patternObj->getPatternCharArray();
        $this->assertNotEmpty($patternChars);
        $this->assertEquals($matchesCount, count($patternChars),
            "Pattern $pattern must have $matchesCount patternChars array elements");
    }

    public function provider(): PatternFileIterator
    {
        $fileName = (file_exists(PatternDataLoader::DEFAULT_FILENAME)) ?
            PatternDataLoader::DEFAULT_FILENAME : '../' . PatternDataLoader::DEFAULT_FILENAME;
        return new PatternFileIterator($fileName);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->logger = $this->createMock(Logger::class);
        $this->config = $this->createMock(Config::class);
        $this->cache = $this->createMock(FileCache::class);
        $this->config
            ->method('getDbConfig')
            ->willReturn(DbConfig::getInstance(
                'localhost',
                'word_hyphenation_db',
                'root',
                'Q1w5e9r7t5y3@',
                $this->logger, true));
        $this->config
            ->method('isEnabledDbSource')
            ->willReturn(true);
        $this->dbPatterns = $this->getMockBuilder(DbPatterns::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs(array($this->config, $this->logger, $this->cache))
            ->getMock();
    }

}