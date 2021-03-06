DROP TABLE IF EXISTS post;

CREATE TABLE post (
	post_id bigint(10) UNSIGNED NOT NULL auto_increment,
	post_title TEXT NOT NULL,
	post_content LONGTEXT NOT NULL,
	post_creationdate DATETIME NOT NULL,
	PRIMARY KEY (post_id)
) ENGINE = InnoDB;
