<?php
class SpecialPmaTree extends SpecialPage {
  var $dbr;
  var $pmas;
  var $output;
  function __construct() {
    parent::__construct( 'PmaTree' );
    $this->mIncludable = true;

  }

  function getGroupName() {
    return 'wiki';
  }

  function header($elem, $count)
  {
    return str_repeat('=',$count) . $this->render_with_type($elem) . str_repeat('=',$count) . "\n";
  }

  function pounds($elem, $count)
  {
    return str_repeat('#',$count) . $this->render_with_type($elem). "\n";
  }

  function render_with_type_russian($elem)
  {
    if ($elem->type === NULL){
      return '[[' . $this->remove_underscores($elem->ru_name) . ']]';
    }
    $type_readable = Pma::$type_maps[$elem->type];
    $output;
    switch ($type_readable) {
        case 'algorithm':
            $output = '[[File:A-chameleon-square-64x64.png|16px|link=Project:Уровни классификации|Уровень алгоритма]]';
            return $output.'[['. $this->remove_underscores($elem->ru_name).']]';
        case 'problem':
            $output = '[[File:З-orange-square-64x64.png|16px|link=Project:Уровни классификации|Уровень задачи]]';
            return $output.'[['. $this->remove_underscores($elem->ru_name).']]';
        case 'method':
            $output = '[[File:M-butter-square-64x64.png|16px|link=Project:Уровни классификации|Уровень метода]]';
            return $output.'[['. $this->remove_underscores($elem->ru_name).']]';
            break;
        case 'implementation':
            return $this->msg('pmatree-implementation').$this->remove_underscores($elem->ru_name);
        case 'without_page':
            return $this->remove_underscores($elem->ru_name);
        case 'without_page_and_header':
            return $this->remove_underscores($elem->ru_name);

    }
  }

  function render_with_type_english($elem)
  {
    if ($elem->type === NULL){
      return '[[' . $this->remove_underscores($elem->en_name) . ']]';
    }
    $type_readable = Pma::$type_maps[$elem->type];
    $output;
    switch ($type_readable) {
        case 'algorithm':
            $output = '[[File:A-chameleon-square-64x64.png|16px|link=Project:Levels of classification|Algorithm level]]';
            return $output.'[['. $this->remove_underscores($elem->en_name).']]';
        case 'problem':
            $output = '[[File:З-orange-square-64x64.png|16px|link=Project:Levels of classification|Problem level]]';
            return $output.'[['. $this->remove_underscores($elem->en_name).']]';
        case 'method':
            $output = '[[File:M-butter-square-64x64.png|16px|link=Project:Levels of classification|Method level]]';
            return $output.'[['. $this->remove_underscores($elem->en_name).']]';
            break;
        case 'implementation':
            return $this->msg('pmatree-implementation').$this->remove_underscores($elem->en_name);
        case 'without_page':
            return $this->remove_underscores($elem->en_name);
        case 'without_page_and_header':
            return $this->remove_underscores($elem->en_name);

    }
  }

  function isRussian(){
    return ($GLOBALS['wgContLang']->getCode()==='ru');
  }

  function render_with_type($elem)
  {
    if($this->isRussian()){
      return $this->render_with_type_russian($elem);
    }
    else{
      return $this->render_with_type_english($elem);
    }
  }

  function remove_underscores($arg)
  {
    return str_replace("_", " ", $arg);
  }


  function render_element($pma,$level,$last_without_page = 0,$parent = NULL){
    if($pma->type == '3'){
      $this->output.= $this->header($pma,$level);
      $last_without_page = $level;
    }
    elseif($pma->type == '4'){
      $this->output.= $this->pounds($pma,$level - $last_without_page);
    }
    else{
      $this->output.= $this->pounds($pma,$level - $last_without_page);
    }
    if($pma->childs_ids)
      foreach (explode(',',$pma->childs_ids) as $child_id ) {
        $this->render_element(Pma::find_or_raise_exception($this->pmas, 'id',$child_id),$level + 1,$last_without_page,$pma);
      }
  }

  function edit($from_update = 'no'){
    if($from_update == 'no' && $this->getRequest()->getText('parent_id')){
        $from_update = json_encode(array("a" => "new","parent_id" => $this->getRequest()->getText('parent_id')));
    }
    elseif($from_update == 'no'){
      $from_update = json_encode(array("a" => "new"));
    }
    $this-> getOutput()->addHtml(file_get_contents(__DIR__ . '/js/libraries.html'));
    $this-> getOutput()->addHtml('<div id="pma-tree-top"></div>');
    $this-> getOutput()->addHtml('<div id="pma-tree-bottom"></div>');
    $pmas_json =  json_encode($this->pmas);
    $type_maps = json_encode(Pma::$type_maps);
    ob_start();
     include __DIR__ . '/js/bottomForm.js';
     $include = ob_get_contents();
    ob_end_clean();
    $this-> getOutput()->addHtml('<script>' . $include. '</script>');
    ob_start();
     include __DIR__ . '/js/form.js';
     $include = ob_get_contents();
    ob_end_clean();
    $this-> getOutput()->addHtml('<script>' . $include. '</script>');
  }

  function update(){
    $request = $this->getRequest();
    $attrs = array();
    foreach (['en_short','ru_short','ru_name','en_name','id','type','perform_delete'] as $value)
      $attrs[$value] = $request->getText($value);
    if($request->getText('hidden_parents_ids')){
      $attrs['parents_ids'] = array_unique(explode(',',$request->getText('hidden_parents_ids')));
    }
    else{
      $attrs['parents_ids'] = [];
    }
    $res = Pma::update($attrs);
    $pmas_results = Pma::selectAllWithCategories();
    foreach($pmas_results as $pma)
      $this->pmas[]= $pma;
    if($res == 'success'){
      $resHtml = "<div class='alert alert-success'>{$this->msg('pmatree-success')}</div>";
    }
    else{
      $resHtml = "<div class='alert alert-danger'>{$this->msg('pmatree-error-'.$res)} </div>";
    }
    $this-> getOutput()->addHtml($resHtml);
    $attrs['ru_name'] = $this->remove_underscores($attrs['ru_name']);
    $attrs['en_name'] = $this->remove_underscores($attrs['en_name']);
    $this->edit(json_encode($attrs));
  }

  function execute( $par ) {

    $this->output= '';
    $request = $this->getRequest();
    $this->setHeaders();
    if($request->getText('action')){
      if(!in_array('pmatree_edit', $this->getUser()->getRights())){
        $this->displayRestrictionError();
		    return;
      }
      else{
        $this->getOutput()->addHtml('<a href="/ru/Special:PMA_Tree">' .Xml::submitButton( $this->msg( 'pmatree-show' )).'</a>');
      }
    }


    if ($request->getText('action') == 'update'){
      $this->update();
      return;
    }
    $pmas_results = Pma::selectAllWithCategories();
    foreach($pmas_results as $pma)
      $this->pmas[]= $pma;
    if ($request->getText('action') == 'edit'){
      $this->edit();
      return;
    }
    // if ($request->getText('action') == 'new'){
    //   $this->new();
    //   return;
    // }

    if(in_array('pmatree_edit', $this->getUser()->getRights()) && $this->isRussian()){
      $this->getOutput()->addHtml('<a href="/ru/Special:PMA_Tree?action=edit">' .Xml::submitButton( $this->msg( 'pmatree-edit' ),
      			[ 'id' => 'pmatreesubmit', 'name' => 'pmatreesubmit' ] ) .'</a>');
    }

    $inits = Pma::get_sub_array($this->pmas, 'parents_ids', NULL);
    foreach($inits as $pma){
      $this->render_element($pma,1);
    }
    $this-> getOutput()->addWikiText($this->output);
  }
}
