<?php
class Pma {
  var $output;
  var $type;
  var $ru_page_id;
  var $en_page_id;
  var $ru_name;
  var $en_name;

  public static $type_maps=array(
    '0' => 'algorithm',
    '1' => 'problem',
    '2' => 'method',
    '3' => 'without_page',
    '4' => 'without_page_and_header',
    '5' => 'implementation'
  );

  public static $pmas=array();


  public static function add_underscores($arg)
  {
    return str_replace(" ", "_", $arg);
  }

  public static function &find_or_raise_exception($array, $field, $searched_value)
  {
     foreach($array as $key => $value)
     {
        if ( $value->$field === $searched_value )
           return $value;
     }
     throw new Exception("no value with this field {$field} = {$searched_value}");
  }

  public static function find_in_array($array, $field, $searched_value)
  {
     foreach($array as $key => $value)
     {
        if ( $value[$field] === $searched_value )
           return $value;
     }
     return NULL;
  }

  public static function color_black(&$array, $field, $searched_value)
  {
     foreach($array as $key => $value)
     {
        if ( $value[$field] === $searched_value )
           $array[$key]['color'] = 'black';
     }
  }





  public static function find_without_exception($array, $field, $searched_value)
  {
     foreach($array as $key => $value)
     {
        if ( $value->$field === $searched_value )
           return $value;
     }
     return NULL;
  }


  public static function get_sub_array($array, $field, $searched_value)
  {
    $sub_array = [];
     foreach($array as $key => $value)
     {
        if ( $value->$field === $searched_value )
          $sub_array[]= $value;
     }
    return $sub_array;
  }

  public static function update(&$attrs){
    $attrs['ru_name'] =     Self::add_underscores($attrs['ru_name']);
    $attrs['en_name'] =     Self::add_underscores($attrs['en_name']);
    $pmas = Self::selectAllWithCategories();
    foreach ($pmas as $value) {
      if($value->parents_ids){
        $value->parents_ids = explode(',',$value->parents_ids);
      }
      else{
        $value->parents_ids = array();
      }
      if($value->childs_ids){
        $value->childs_ids = explode(',',$value->childs_ids);
      }
      else{
        $value->childs_ids = array();
      }
      Self::$pmas[]=$value;
    }
    if($attrs['perform_delete'] === 'true'){
      return Self::delete($attrs);
    }
    return Self::oldUpdate($attrs);
  }

  public static function delete(&$attrs){
    if($attrs['id'] == 'new'){
      return "new_delete";
    }
    $pma = Self::find_or_raise_exception(Self::$pmas,'id',$attrs['id']);
    if(count($pma->childs_ids)){
      return "children";
    }
    Self::dbr()->begin();
    Self::dbr()->delete('pma_tree_pma', array('id'=>$attrs['id']));
    Self::dbr()->delete('pma_tree_links', array('child_id'=>$attrs['id']));
    Self::dbr()->commit();
    return true;
  }

  public static function save(&$attrs){
    $attr_names = ['en_short','ru_short','ru_name','en_name','id','type','parents_ids'];
    $updated = FALSE;
    if($attrs['id'] == 'new'){
      $updated = TRUE;
      Self::dbr() -> begin();
      $attrs_up = $attrs;
      unset($attrs_up['id']);
      unset($attrs_up['parents_ids']);
      unset($attrs_up['perform_delete']);
      $attrs_up['updated_at'] = date("Y-m-d H:i:s");
      $attrs_up['created_at'] = date("Y-m-d H:i:s");
      Self::dbr()->insert('pma_tree_pma', $attrs_up);
      foreach(Self::dbr()->query("SELECT id FROM pma_tree_pma ORDER BY ID DESC LIMIT 1") as $row){
        $id = $row->id;
      }
      $attrs['id'] = $id;
      foreach($attrs['parents_ids'] as $par_id ){
        Self::dbr()->insert('pma_tree_links', array('child_id' => $id,'parent_id' => $par_id));
      }
      Self::dbr()->commit();
    }else{
      $pma = Self::find_or_raise_exception(Self::$pmas,'id',$attrs['id']);
      foreach($attr_names as $name){
        if($pma->$name != $attrs[$name]){
          $updated = TRUE;
          break;
        }
      }
      if ($updated){
        Self::dbr() -> begin();
        $attrs_up = $attrs;
        unset($attrs_up['id']);
        unset($attrs_up['parents_ids']);
        unset($attrs_up['perform_delete']);
        $attrs_up['updated_at'] = date("Y-m-d H:i:s");
        Self::dbr()->update('pma_tree_pma', $attrs_up, array('id' => $attrs['id']));
        foreach(array_diff($pma->parents_ids,$attrs['parents_ids']) as $par_id ){
          Self::dbr()->delete('pma_tree_links', array('child_id' => $attrs['id'],'parent_id' => $par_id));
        }
        foreach(array_diff($attrs['parents_ids'],$pma->parents_ids) as $par_id ){
          Self::dbr()->insert('pma_tree_links', array('child_id' => $attrs['id'],'parent_id' => $par_id));
        }
        Self::dbr()->commit();
      }
    }
  }

  public static function oldUpdate(&$attrs){
    $valid = Self::validateAndFill($attrs);
    if($valid === true){
      Self::save($attrs);
      return 'success';
    }
    else{
      return $valid;
    }
  }

  public static function starts_with_upper($str) {
    $chr = mb_substr ($str, 0, 1, "UTF-8");
    return mb_strtolower($chr, "UTF-8") != $chr;
  }

  public static function validateAndFill(&$attrs){
    $type_readable = Self::$type_maps[$attrs['type']];
    if($type_readable !== 'without_page' && $type_readable !== 'without_page_and_header' && $type_readable !== 'implementation')
    {
      if(($el = Self::find_without_exception(Self::$pmas,'ru_name',$attrs['ru_name'])) && $el->id != $attrs['id'] ){
        return 'ru_name_exists';
      }
      if(($el = Self::find_without_exception(Self::$pmas,'en_name',$attrs['en_name'])) && $el->id != $attrs['id'] ){
        return 'en_name_exists';
      }
      $en_page_title = Self::add_underscores(Self::SelectEnPage($attrs['ru_name'])->fetchRow()['ll_title']);
      $ru_page_title = Self::add_underscores(Self::SelectRuPage($attrs['en_name'])->fetchRow()['ll_title']);
      if($attrs['en_name'] !== '' && $en_page_title && $attrs['en_name'] != $en_page_title){
        return 'en_name_wrong';
      }
      if($attrs['ru_name'] !== '' && $ru_page_title && $attrs['ru_name'] !== $ru_page_title){
        return 'ru_name_wrong';
      }
      if(!$en_page_title  && $attrs['en_name'] === ''){
        return 'en_name_empty';
      }
      if(!$ru_page_title  && $attrs['ru_name'] === ''){
        return 'ru_name_empty';
      }
      if($en_page_title && $attrs['en_name'] === '' ){
        $attrs['en_name'] = $en_page_title;
      }
      if($ru_page_title && $attrs['ru_name'] === '' ){
        $attrs['ru_name'] = $ru_page_title;
      }
      if(!count($attrs['parents_ids']) && $attrs['type'] != '3') {
        return 'null_not_without_page';
      }
    }
    else{
      foreach ($attrs['parents_ids'] as $parent_id) {
        if($attrs['type'] == '3' && Self::find_or_raise_exception(Self::$pmas,'id',$parent_id)->type != '3'){
          return 'not_without_page_parent';
        }
      }
      if($attrs['en_name'] === ''){
        return 'en_name_empty';
      }
      if($attrs['ru_name'] === ''){
        return 'ru_name_empty';
      }
    }
    if(!Self::starts_with_upper($attrs['ru_name'])){
      return 'ru_name_capital';
    }
    if(!Self::starts_with_upper($attrs['ru_name'])){
      return 'en_name_capital';
    }
    foreach($attrs['parents_ids'] as $elem){
      $error = Self::check_parents(Self::find_or_raise_exception(Self::$pmas,'id',$elem),$attrs['type']);
      if($error){
        return 'order';
      }
    }
    if($attrs['id']!='new'){
      foreach ($attrs['parents_ids'] as $value) {
        $parent =  &Self::find_or_raise_exception(Self::$pmas,'id',$value);
        $childs_array = $parent->childs_ids;
        if(!in_array($attrs['id'],$childs_array)){
          array_push($childs_array,$attrs['id']);
          $parent->childs_ids = $childs_array;
        }
      }
      $ids = [];
      foreach(Self::get_sub_array(Self::$pmas,'parents_ids',array()) as $elem){
        $cycle = Self::check_cycles($elem,$ids);
        if($cycle)
        {
          return 'cycle';
        }
      }
    }
    return true;
  }
  public static function check_parents($elem,$type){
   $order = ['1','2','0','5'];
   if(array_search($type,$order) !== NULL && array_search($elem->type,$order) !== NULL  && array_search($type,$order) < array_search($elem->type,$order))
   {
     return $elem;
   }
   foreach($elem->parents_ids as $parent){
     $error = Self::check_parents(Self::find_or_raise_exception(Self::$pmas,'id',$parent),$type);
     if($error){
       return $error;
     }
   }
   return NULL;
  }

  public static function check_cycles($elem,&$ids){
    $vertex = Self::find_in_array($ids,'id',$elem->id);
    if($vertex && $vertex['color'] === 'grey'){
      return $vertex;
    }
    if(!$vertex){
      $vertex = array('id' => $elem->id, 'color' => 'grey');
      array_push($ids,$vertex);
      foreach($elem->childs_ids as $child_id){
        $cycle = Self::check_cycles(Self::find_or_raise_exception(Self::$pmas,'id',$child_id),$ids);
        if($cycle){
          return $cycle;
        }
      }
    }
    Self::color_black($ids,'id',$elem->id);
    return NULL;
  }

  public static function SelectEnPage($title)
  {
    return Self::dbr()->query("SELECT p.page_id,langlinks.ll_title FROM page AS p
                               INNER JOIN langlinks ON
                               langlinks.ll_from = p.page_id
                               WHERE p.page_title = '$title'"
                              );
  }

  public static function SelectRuPage($title)
  {
    return Self::dbrEn()->query("SELECT p.page_id,langlinks.ll_title FROM page AS p
                               INNER JOIN langlinks ON
                               langlinks.ll_from = p.page_id
                               WHERE p.page_title = '$title'"
                              );
  }



  public static function selectAllWithCategories()
  {
    return Self::dbr()->query("SELECT p.ru_name,p.en_name,p.ru_short,p.en_short,
                               p.type, p.id, p.created_at, p.updated_at,
                               GROUP_CONCAT(DISTINCT pma_links.child_id) AS childs_ids,
                               GROUP_CONCAT(DISTINCT parent_links.parent_id)  as parents_ids
                               FROM pma_tree_pma as p
                               LEFT JOIN pma_tree_links as pma_links ON
                               pma_links.parent_id = p.id
                               LEFT JOIN pma_tree_links as parent_links ON
                               parent_links.child_id = p.id
                               GROUP BY id"
                              );
  }
  public static function dbr(){
    return wfGetDB( DB_MASTER,[],'algowiki_ru');
  }

  public static function dbrEn(){
    return wfGetDB( DB_SLAVE,[],'algowiki_en' );
  }

  public static function truncateTable() {
    Self::dbr() ->delete('pma_tree_pma','*');
    Self::dbr() ->delete('pma_tree_links','*');
  }
}
