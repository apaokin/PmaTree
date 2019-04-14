CREATE TABLE pma_tree_log (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  type int(2),
  status varchar(10),
  user_id int(10),
  user_name varchar(100),
  child_id int(10) unsigned NOT NULL,
  child_ru_name varchar(2000),
  child_en_name varchar(2000),
  parent_id int(10) unsigned NOT NULL,
  parent_ru_name varchar(2000),
  parent_en_name varchar(2000),
  PRIMARY KEY (id),
  KEY parent_id (parent_id),
	KEY child_id (child_id)
) DEFAULT CHARSET=binary;
