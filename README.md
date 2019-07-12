# WORD HYPHENATION PHP CLI
## Database Configuration
```bash
 php word_hyphenation.php --config-db [db_host] [db_name] [db_user] [db_password]
 #configure database and enable database source
 php word_hyphenation.php --db-import-patterns-file [pattern_file_path]
 #import patterns file to database
```
## More CLI commands
```bash
 php word_hyphenation.php -w [word] [save_result_to_file(optional)] 
 #hyphenate one word
 php word_hyphenation.php -p [paragraph / sentence] [save_result_to_file(optional)]
 #hyphenate one paragraph / sentence
 php word_hyphenation.php -f [read_file] [save_result_to_file(optional)]
 #hyphenate all text from file
 php word_hyphenation.php --clear cache
 #clean Cache Storage
 php word_hyphenation.php --clear log
 #clean log file
```


(c) Vaclovas Lapinskis 2019
