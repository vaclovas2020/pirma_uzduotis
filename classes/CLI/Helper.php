<?php

namespace CLI;

class Helper
{
    public function printHelp(): void
    {
        echo "Use command 'php word_hyphenation.php -w [word] [save_result_to_file(optional)]' if you want to hyphenate one word.\n";
        echo "Use command 'php word_hyphenation.php -p [paragraph / sentence] [save_result_to_file(optional)]' if you want to hyphenate paragraph / sentence.\n";
        echo "Use command 'php word_hyphenation.php -f [read_file] [save_result_to_file(optional)]' if you want to hyphenate all text from given file.\n";
        echo "Use command 'php word_hyphenation.php --clear cache' if you want to clean Cache Storage.\n";
        echo "Use command 'php word_hyphenation.php --clear log' if you want to delete log file.\n";
    }
}
