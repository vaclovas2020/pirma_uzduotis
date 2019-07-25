<?php


use AppConfig\Config;
use Hyphenation\WordHyphenationTool;
use Log\Logger;
use PHPUnit\Framework\TestCase;
use SimpleCache\FileCache;

class WordHyphenationToolTest extends TestCase
{

    public function testHyphenateWord()
    {
        $logger = new Logger();
        $cache = new FileCache();
        $config = new Config($logger);
        $hyphenationTool = new WordHyphenationTool($logger, $cache, $config);
        $this->assertEquals('mis-trans-late', $hyphenationTool->hyphenateWord('mistranslate'),
            "Test failed: mistranslate not hyphenated to mis-trans-late");
    }
}