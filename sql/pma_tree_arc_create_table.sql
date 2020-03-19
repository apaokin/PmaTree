CREATE TABLE pma_tree_arc (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  arc_id int(10),
	el_id int(10) unsigned NOT NULL,
  PRIMARY KEY (id),
  KEY el_id (el_id)
);
