<?php

namespace CLI;

class Helper
{
    public function printHelp(): void
    {
        echo "Use command 'php word_hyphenation.php --config-db [db_host] [db_name] [db_user] [db_password]' if you want to configure database.\n";
        echo "Use command ' php word_hyphenation.php --db-import-patterns-file [pattern_file_path]' if you want to import patterns file to database.\n";
        echo "Use command 'php word_hyphenation.php -w [word] [save_result_to_file(optional)]' if you want to hyphenate one word.\n";
        echo "Use command 'php word_hyphenation.php -p [paragraph / sentence] [save_result_to_file(optional)]' if you want to hyphenate paragraph / sentence.\n";
        echo "Use command 'php word_hyphenation.php -f [read_file] [save_result_to_file(optional)]' if you want to hyphenate all text from given file.\n";
        echo "Use command 'php word_hyphenation.php --patterns [word]' if you want to get patterns list of word (works only if database source is configured)\n";
        echo "Use command 'php word_hyphenation.php --clear cache' if you want to clean Cache Storage.\n";
        echo "Use command 'php word_hyphenation.php --clear log' if you want to clean log file.\n";
    }
}
