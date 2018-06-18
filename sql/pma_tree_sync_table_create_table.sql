CREATE TABLE pma_tree_sync (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  eng_page_id int(11),
	ru_page_id int(11),
	top53_id int(11),
	page_type char(40),
  PRIMARY KEY (id),
  UNIQUE KEY eng_page_id (eng_page_id),
	UNIQUE KEY ru_page_id (ru_page_id)
);
