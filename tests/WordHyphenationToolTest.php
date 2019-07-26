<?php


use AppConfig\Config;
use AppConfig\DbConfig;
use Hyphenation\WordHyphenationTool;
use Log\Logger;
use PHPUnit\Framework\TestCase;
use SimpleCache\FileCache;

class WordHyphenationToolTest extends TestCase
{

    public function testHyphenateWord(): void
    {
        $logger = $this->createMock(Logger::class);
        $cache = $this->createMock(FileCache::class);
        $config = $this->createMock(Config::class);
        $config
            ->method('getDbConfig')
            ->willReturn(DbConfig::getInstance(
                'localhost',
                'word_hyphenation_db',
                'root',
                'Q1w5e9r7t5y3@',
                $logger, true));
        $hyphenationTool = new WordHyphenationTool($logger, $cache, $config);
        $this->assertEquals('mis-trans-late', $hyphenationTool->hyphenateWord('mistranslate'),
            "Test failed: mistranslate not hyphenated to mis-trans-late");
        $this->assertEquals('net-work', $hyphenationTool->hyphenateWord('network'),
            "Test failed: network not hyphenated to net-work");
        $this->assertEquals('cat-fish', $hyphenationTool->hyphenateWord('catfish'),
            "Test failed: catfish not hyphenated to cat-fish");
    }

    public function testGetFoundPatternsOfWord(): void
    {
        $logger = $this->createMock(Logger::class);
        $cache = $this->createMock(FileCache::class);
        $config = $this->createMock(Config::class);
        $config
            ->method('getDbConfig')
            ->willReturn(DbConfig::getInstance(
                'localhost',
                'word_hyphenation_db',
                'root',
                'Q1w5e9r7t5y3@',
                $logger, true));
        $hyphenationTool = new WordHyphenationTool($logger, $cache, $config);
        $this->assertNotEmpty($hyphenationTool->getFoundPatternsOfWord('mistranslate'),
            "getFoundPatternsOfWord return empty array");
    }
}