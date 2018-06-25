<?php
$basePath = getenv( 'MW_INSTALL_PATH' ) !== false ? getenv( 'MW_INSTALL_PATH' ) : '/var/www/algowiki/ru' ;
require_once $basePath . '/maintenance/Maintenance.php';
class PmaUpdater extends Maintenance {

  public static function dbr(){
    return wfGetDB( DB_MASTER,[],'algowiki_ru');
  }

  public static function dbrEn(){
    return wfGetDB( DB_SLAVE,[],'algowiki_en' );
  }

  public function addUndescores($arg) {
		$res = str_replace(" ", "_", $arg);
		return $res;
  }

  public function findByTitleWithCategories($dbr, $title){
    return $dbr->query("SELECT *, categorylinks.cl_to  FROM `page` LEFT JOIN `categorylinks` ON
            ((cl_from = page_id))
           WHERE page_title = \"$title\"");
  }

  public function findByRuTitleWithCategories($title){
    return $this->findByTitleWithCategories(Self::dbr(),$title);
  }

  public function findByEnTitleWithCategories($title){
    return $this->findByTitleWithCategories(Self::dbrEn(),$title);
  }

  public function execute() {
    Pma::truncateTable();
    $linesToIds = array();
    if (($handle = fopen(__DIR__ . "/PmaTreePma.csv", "r")) !== FALSE) {
        fgetcsv($handle, 1000, ",");
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
					$line = $data[0];
          $ru_name = $this->addUndescores($data[1]);
          $en_name = $this->addUndescores($data[2]);
          $without_page = $data[3];
          $parent = $data[4];
          $ru_page_categories = $this->findByRuTitleWithCategories($ru_name);
					$type_readable = NULL;
					$pma_id = Self::dbr()->selectField('pma_tree_pma','id','ru_name = "'.$ru_name.'"');
          if($pma_id && $parent!=='' && $data[1] != 'Другие методы' && $data[1] != 'Методы, основанные на стандартном LU-разложении матрицы')
	 				{
						print $ru_name . "\n";
	 					 Self::dbr() ->insert('pma_tree_links',array('parent_id' => $linesToIds[$parent],
	 																											'child_id' => $pma_id,
	 																										));
						$linesToIds[$line] = $pma_id;
						continue;
	 				}
          if($without_page !== 'false'){
            $type_readable = $without_page;
          }else{
            if ($ru_page_categories->numRows())
            {
              foreach ($ru_page_categories as $cat){
                if($cat->cl_to === 'Уровень_алгоритма')
									$type_readable = 'algorithm';
								if($cat->cl_to === 'Уровень_задачи')
									$type_readable = 'problem';
								if($cat->cl_to === 'Уровень_метода')
									$type_readable = 'method';
              }
            }
          }
					$type = array_search($type_readable, Pma::$type_maps);
					if ($type === false && $type_readable !== NULL)
						throw new Exception('no type');
          if ($type === false){
            $type= NULL;
          }
					Self::dbr() ->insert('pma_tree_pma',array('ru_name' => $ru_name,
                                                     'en_name' => $en_name,
                                                     'created_at' => date("Y-m-d H:i:s"),
                                                     'updated_at' => date("Y-m-d H:i:s"),
                                                     'type' => $type,
                                                   ));
					$pma_id = Self::dbr()->query("SELECT MAX(id) as max_id  FROM pma_tree_pma WHERE ru_name = \"$ru_name\"")->fetchRow()['max_id'];
					if (!$pma_id)
						throw new Exception('PMA_ID');
					$linesToIds[$line] = $pma_id;
					if($parent!=='')
					{
						 Self::dbr() ->insert('pma_tree_links',array('parent_id' => $linesToIds[$parent],
																												'child_id' => $pma_id
																											));
					}

        }


        fclose($handle);
    }
  }
}
$maintClass = PmaUpdater::class;
require_once RUN_MAINTENANCE_IF_MAIN;
