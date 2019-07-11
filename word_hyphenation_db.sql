
CREATE TABLE `hyphenated_words` (
  `word_id` int(11) NOT NULL,
  `word` varchar(255) COLLATE utf8_bin NOT NULL,
  `hyphenated_word` varchar(255) COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE `hyphenated_word_patterns` (
  `word_id` int(11) NOT NULL,
  `pattern_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE `hyphenation_patterns` (
  `pattern_id` int(11) NOT NULL,
  `pattern` varchar(255) COLLATE utf8_bin NOT NULL,
  `pattern_chars` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

ALTER TABLE `hyphenated_words`
  ADD PRIMARY KEY (`word_id`),
  ADD UNIQUE KEY `word` (`word`),
  ADD UNIQUE KEY `hyphenated_word` (`hyphenated_word`);

ALTER TABLE `hyphenated_word_patterns`
  ADD PRIMARY KEY (`word_id`,`pattern_id`),
  ADD KEY `word_pattern_id_key` (`pattern_id`);

ALTER TABLE `hyphenation_patterns`
  ADD PRIMARY KEY (`pattern_id`),
  ADD UNIQUE KEY `pattern` (`pattern`);

ALTER TABLE `hyphenated_words`
  MODIFY `word_id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `hyphenation_patterns`
  MODIFY `pattern_id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `hyphenated_word_patterns`
  ADD CONSTRAINT `word_id_key` FOREIGN KEY (`word_id`) REFERENCES `hyphenated_words` (`word_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `word_pattern_id_key` FOREIGN KEY (`pattern_id`) REFERENCES `hyphenation_patterns` (`pattern_id`) ON DELETE CASCADE ON UPDATE CASCADE;
