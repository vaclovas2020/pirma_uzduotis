<?php


use AppConfig\Config;
use DB\DbPatterns;
use Hyphenation\Pattern;
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
     * @param int $count
     */
    public function testGetPatternCharArray(string $pattern, int $count): void
    {
        $patternObj = new Pattern($this->config, $this->dbPatterns, $pattern);
        $patternChars = $patternObj->getPatternCharArray();
        $this->assertNotEmpty($patternChars);
        $this->assertEquals($count, count($patternChars),
            'Pattern ' . $pattern . ' must have ' . $count . ' patternChars array elements');
    }

    public function provider(): array
    {
        return [
            ['ach4', 1],
            ['ad4der', 1],
            ['af1t', 1],
            ['a3t2l', 2],
            ['5at', 1]
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->logger = $this->createMock(Logger::class);
        $this->config = $this->createMock(Config::class);
        $this->cache = $this->createMock(FileCache::class);
        $this->dbPatterns = $this->createMock(DbPatterns::class);
    }

}