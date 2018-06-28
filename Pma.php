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


	public static function find_or_raise_exception($array, $field, $searched_value)
  {
     foreach($array as $key => $value)
     {
        if ( $value->$field === $searched_value )
           return $value;
     }
     throw new Exception('no value with this field');
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


  public function beforeValidate()
  {
    // if($ru_page_id){
    //   $ru_name = null;
    // }
    // if($en_page_id){
    //   $en_name = null;
    // }
  }



  public function validate()
  {
    if ($type != NULL && !in_array($type,array_values(self::$type_maps)))
      return "type error";
  }

	public static function update($attrs){
		if($attrs['id'])
			Self::oldUpdate($attrs);
	}

	public static function oldUpdate($attrs){
		$pmas = selectAllWithCategories();
		foreach ($pmas as $value) {
			Self::$pmas[]=$value;
		}
		$pma = Self::dbr()->selectRow('pma_tree_pma','*',"id={$attrs['id']}");
	}


	public static function find_children_with_id($elem, $id){
	  $found = NULL;
		foreach(explode(',',$elem['childs_ids']) as $child_id ){
			$pma = find_or_raise_exception(Self::$pmas,'id',$child_id);
			if($pma->id == $id)
	      return $pma;
      if(!$found)
        $found = find_children_with_id(pma,id);
	  }
	  return $found;
	}

	public static function selectAllWithCategories()
	{
		return Self::dbr()->query("SELECT p.ru_name,p.en_name,
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
