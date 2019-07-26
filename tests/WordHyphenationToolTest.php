<?php


use AppConfig\Config;
use AppConfig\DbConfig;
use Hyphenation\WordHyphenationTool;
use Log\Logger;
use PHPUnit\Framework\TestCase;
use SimpleCache\FileCache;

class WordHyphenationToolTest extends TestCase
{

    private $logger;
    private $cache;
    private $config;

    /**
     * @dataProvider provider
     * @param string $word word
     * @param string $hyphenatedWord hyphenated word
     */
    public function testHyphenateWord(string $word, string $hyphenatedWord): void
    {
        $hyphenationTool = new WordHyphenationTool($this->logger, $this->cache, $this->config);
        $this->assertEquals($hyphenatedWord, $hyphenationTool->hyphenateWord($word),
            "Test failed: $word not hyphenated to $hyphenatedWord");
    }

    /**
     * @dataProvider provider
     * @param string $word
     */
    public function testGetFoundPatternsOfWord(string $word): void
    {
        $hyphenationTool = new WordHyphenationTool($this->logger, $this->cache, $this->config);
        $this->assertNotEmpty($hyphenationTool->getFoundPatternsOfWord($word),
            "getFoundPatternsOfWord return empty array when given word is $word");
    }

    public function testHyphenateAllText(): void
    {
        $text = '45 78 mistranslate and catfish 45,78! network forever is done.';
        $hyphenatedText = '45 78 mis-trans-late and cat-fish 45,78! net-work for-ever is done.';
        $hyphenationTool = new WordHyphenationTool($this->logger, $this->cache, $this->config);
        $this->assertEquals($hyphenatedText, $hyphenationTool->hyphenateAllText($text),
            "Test failed: '$text' not hyphenated to '$hyphenatedText'");
    }

    public function provider(): array
    {
        return array(
            array('mistranslate', 'mis-trans-late'),
            array('catfish', 'cat-fish'),
            array('network', 'net-work'),
            array('workshop', 'work-shop'),
            array('forever', 'for-ever')
        );
    }

    protected function setup(): void
    {
        $this->logger = $this->createMock(Logger::class);
        $this->cache = $this->createMock(FileCache::class);
        $this->config = $this->createMock(Config::class);
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
    }
}