<?php
class Sync {
  var $counter;
	var $counterEn;
	var $output;
  public static function dbr(){
    return wfGetDB( DB_MASTER,[],'algowiki_ru');
  }

  public static function dbrEn(){
    return wfGetDB( DB_REPLICA,[],'algowiki_en' );
  }

  public static function truncateTable() {
    Self::dbr() ->delete('pma_tree_sync','*');
  }


  public static function Top53IdByEnPageId($id){
    $res = Self::dbr()->selectField(
            'pma_tree_sync',
            'top53_id',
            'eng_page_id ='. $id ,
            __METHOD__
          );
    return $res;
  }

  public static function Top53IdByRuPageId($id){
    $res = Self::dbr()->selectField(
            'pma_tree_sync',
            'top53_id',
            'ru_page_id ='. $id ,
            __METHOD__
          );
    return $res;
  }

  public static function findByTitle($dbr, $title){
    $res = $dbr->selectField(
            'page',
            'page_id',
            'page_title = "' . $title .'"' ,
            __METHOD__
          );
    if(!$res){
      return NULL;
    }
    return $res;
  }

  public static function findByEnTitle($title){
    return Self::findByTitle(Self::dbrEn(),$title);
  }

  public static function findByRuTitle($title){
    return Self::findByTitle(Self::dbr(),$title);
  }


  public static function addRow($en,$ru, $top53Id) {

    $ruId = Self::findByRuTitle($ru);
    $enId = Self::findByEnTitle($en);
    Self::dbr() ->insert('pma_tree_sync',array('eng_page_id' => $enId,
                                               'ru_page_id' => $ruId,
                                                'top53_id' => $top53Id));
  }
}
