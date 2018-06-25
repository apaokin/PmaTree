CREATE TABLE pma_tree_links (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  parent_id int(11),
	child_id int(11),
  PRIMARY KEY (id),
  KEY parent_id (parent_id),
	KEY child_id (child_id)
);
