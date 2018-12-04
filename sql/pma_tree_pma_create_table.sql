CREATE TABLE pma_tree_pma (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  ru_name varchar(2000),
  en_name varchar(2000),
  ru_short varchar(255),
  en_short varchar(255),
  created_at datetime,
  updated_at datetime,
  type int(2),
  PRIMARY KEY (id),
  KEY en_name_key (en_name),
	KEY ru_name_key (ru_name)
) DEFAULT CHARSET=binary;
