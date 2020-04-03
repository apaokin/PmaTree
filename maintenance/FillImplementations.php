<?php
$basePath = getenv( 'MW_INSTALL_PATH' ) !== false ? getenv( 'MW_INSTALL_PATH' ) : '/var/www/algowiki/ru' ;
require_once $basePath . '/maintenance/Maintenance.php';
class ImpUpdater extends Maintenance {
  public static function dbr(){
    return wfGetDB( DB_MASTER,[],'algowiki_ru');
  }

  public static function dbrEn(){
    return wfGetDB( DB_REPLICA,[],'algowiki_en' );
  }

  public function addUndescores($arg) {
    $res = str_replace(" ", "_", $arg);
    return $res;
  }

  public static function getText($title){
    return Self::dbr()->query("SELECT t.old_text AS 'text' FROM  page p
                              INNER JOIN revision r ON p.page_latest = r.rev_id
                              INNER JOIN text t ON r.rev_text_id = t.old_id
                              Where p.page_title='$title'");
  }


  public function execute() {
    $text = Self::getText('Алгоритм_Дейкстры')->fetchRow()['text'];
    $after = "=== Существующие реализации алгоритма ===";
    $before = "== Литература ==";
    $text2 = substr($text, strpos($text, $after) + strlen($after));
    $arr = explode($before, $text2, 2);
    $text = $arr[0];
    $magic=2;
    // echo $text;

    // $array = preg_match('/(\*)+(.)+(\*)/', $text, $matches);
    print($text);
    // preg_match_all('/\*[.\s]+\*/', $text, $matches);
    preg_match_all("/\*+[^\*]+\\n/", $text, $matches);
    print_r($matches[0]);
    // foreach($array as $val){
    //   if (strlen($val)>=2)
    // }

    // preg_match('/=== Существующие реализации алгоритма ===/', $text, $matches);
    //
    // var_dump($matches);
    //

  }
}
$maintClass = ImpUpdater::class;
require_once RUN_MAINTENANCE_IF_MAIN;
