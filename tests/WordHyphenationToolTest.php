<?php


use AppConfig\Config;
use Hyphenation\WordHyphenationTool;
use Log\Logger;
use PHPUnit\Framework\TestCase;
use SimpleCache\FileCache;

class WordHyphenationToolTest extends TestCase
{

    private $logger;
    private $cache;
    private $config;
    private $patternLoader;

    /**
     * @dataProvider provider
     * @param string $word word
     * @param string $hyphenatedWord hyphenated word
     * @param array $patterns
     */
    public function testHyphenateWord(string $word, string $hyphenatedWord, array $patterns): void
    {
        $this->patternLoader
            ->expects($this->any())
            ->method('getPatternsArray')
            ->willReturn($patterns);
        $hyphenationTool = new WordHyphenationTool($this->logger, $this->cache, $this->config, $this->patternLoader);
        $this->assertEquals($hyphenatedWord, $hyphenationTool->hyphenateWord($word),
            "Test failed: $word not hyphenated to $hyphenatedWord");
    }

    public function testHyphenateAllText(): void
    {
        $patterns = [
            '.mis1', 'a2n', 'm2is', '2n1s2', 'n2sl', 's1l2', 's3lat', 'st4r', '4te.', '1tra', // mistranslate patterns
            '.ca4t', '1ca', '1fi', 's2h', '2sh.', '2t1f', // catfish patterns
            'tw4', '4two', '1wo2', // network patterns
            'ev1er', '1fo', 'fo2r', 'rev2', 'r5ev5er.' // forever patterns
        ];
        $text = '45 78 mistranslate and catfish 45,78! network forever is done.';
        $hyphenatedText = '45 78 mis-trans-late and cat-fish 45,78! net-work for-ever is done.';
        $this->patternLoader
            ->expects($this->any())
            ->method('getPatternsArray')
            ->willReturn($patterns);
        $hyphenationTool = new WordHyphenationTool($this->logger, $this->cache, $this->config, $this->patternLoader);
        $this->assertEquals($hyphenatedText, $hyphenationTool->hyphenateAllText($text),
            "Test failed: '$text' not hyphenated to '$hyphenatedText'");
    }

    public function provider(): array
    {
        return [
            ['mistranslate', 'mis-trans-late',
                ['.mis1', 'a2n', 'm2is', '2n1s2', 'n2sl', 's1l2', 's3lat', 'st4r', '4te.', '1tra']],
            ['catfish', 'cat-fish',
                ['.ca4t', '1ca', '1fi', 's2h', '2sh.', '2t1f']],
            ['network', 'net-work',
                ['tw4', '4two', '1wo2']],
            ['workshop', 'work-shop',
                ['4k1s2', 's2h', 'sho4', '1wo2']],
            ['forever', 'for-ever',
                ['ev1er', '1fo', 'fo2r', 'rev2', 'r5ev5er.']]
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->logger = $this->createMock(Logger::class);
        $this->cache = $this->createMock(FileCache::class);
        $this->config = $this->createMock(Config::class);
        $this->patternLoader = $this->createMock(\Hyphenation\PatternLoaderProxy::class);
        $this->config
            ->expects($this->any())
            ->method('isEnabledDbSource')
            ->willReturn(false);
        $this->config
            ->expects($this->any())
            ->method('getPatternsFilePath')
            ->willReturn('');
    }
}