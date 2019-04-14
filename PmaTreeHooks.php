<?php
class PmaTreeHooks{
  public static function addUndescores($arg) {
    return str_replace(" ", "_", $arg);
  }
  public static function onLoadExtensionSchemaUpdates( DatabaseUpdater $updater ) {
    $updater->addExtensionTable( 'pma_tree_sync',
  		__DIR__ . '/sql/pma_tree_sync_table_create_table.sql' );
    $updater->addExtensionTable( 'pma_tree_pma',
  		__DIR__ . '/sql/pma_tree_pma_create_table.sql' );
    $updater->addExtensionTable( 'pma_tree_links',
  		__DIR__ . '/sql/pma_tree_links_create_table.sql' );
    $updater->addExtensionTable( 'pma_tree_log',
    	__DIR__ . '/sql/pma_tree_log_create_table.sql' );
    return true;
  }

  public static function onParserSetup( &$parser ) {
    $parser->setFunctionHook( 'linktop', 'PmaTreeHooks::renderLinkTop' );
  }

  public static function renderLinkTop( $parser, $pageTitle= ''  ) {
    if($GLOBALS['wgSitename'] == 'Алговики')
    {
      $id = Sync::findByRuTitle(Self::addUndescores($pageTitle));
      $top53_id = Sync::Top53IdByRuPageId($id);
      $categories = Article::newFromId($id)->getCategories();
      if($top53_id)
      {
        foreach($categories as $cat)
        {
          $link = NULL;
          if($cat == 'Категория:Уровень задачи'){
            $link = 'http://top53.parallel.ru/algo_results/task/' . $top53_id;
          }elseif($cat == 'Категория:Уровень алгоритма'){
            $link = 'http://top53.parallel.ru/algo_results/algorithm/' . $top53_id;
          }
          if ($link){
            return "[". $link . " <span style='font-size:10px;' class='mw-ui-button mw-ui-progressive'>Get Perf.Data</span>]";
          }
        }
      }
    }
    else{
      $id = Sync::findByEnTitle(Self::addUndescores($pageTitle));
      $top53_id = Sync::Top53IdByEnPageId($id);
      $categories = Article::newFromId($id)->getCategories();
      if($top53_id)
      {
        foreach($categories as $cat)
        {
          $link = NULL;
          if(Self::compare($cat, 'Category:Problem level‏‎')){
            $link = 'http://top53.parallel.ru/algo_results/task/' . $top53_id;
          }elseif(Self::compare($cat, 'Category:Algorithm level‏‎')){
            $link = 'http://top53.parallel.ru/algo_results/algorithm/' . $top53_id;
          }
          if ($link){
            return "[". $link . " <span style='font-size:10px;' class='mw-ui-button mw-ui-progressive'>Get Perf.Data</span>]";
          }
        }
      }
    }
 }

  public static function linkTemplate($link){
    return '<a style="float: right;" href="' . $link . '">
    <span style="font-size:10px;" class="mw-ui-button mw-ui-progressive">Get Perf.Data</span></a>';
  }

  public static function addImplementationLink($link){
    return '<a style="float: right;" href="' . $link . '">
    <span style="font-size:10px;" class="mw-ui-button mw-ui-progressive">'.wfMessage('pmatree-add-implementation').'</span></a>';
  }


  public static function onArticleViewHeader( &$article, &$outputDone, &$pcache ) {
    $id = $article -> getId();
    $categories = $article->getCategories();
    if($GLOBALS['wgSitename'] == 'Алговики')
    {
      $top53_id = Sync::Top53IdByRuPageId($id);
      $pmaId = Pma::findPmaByRuTitle(Self::addUndescores($article->getTitle()));
      if($pmaId && (in_array('pmatree_edit', $GLOBALS['wgUser']->getRights()))){
        $impUrl = $GLOBALS['wgServer']."/ru/Special:PmaTree?action=edit&parent_id={$pmaId}";
        $article->getContext()->getOutput()->addHTML(Self::addImplementationLink($impUrl));
      };
      if($top53_id)
      {
        foreach($categories as $cat)
        {
          $link = NULL;
          if($cat == 'Категория:Уровень задачи'){
            $link = 'http://top53.parallel.ru/algo_results/task/' . $top53_id;
          }elseif($cat == 'Категория:Уровень алгоритма'){
            $link = 'http://top53.parallel.ru/algo_results/algorithm/' . $top53_id;
          }
          if ($link){
            $article->getContext()->getOutput()->addHTML(self::linkTemplate($link));
          }
        }
      }
    }
    else{
      $top53_id = Sync::Top53IdByEnPageId($id);
      $pmaId = Pma::findPmaByEnTitle(Self::addUndescores($article->getTitle()));
      if($pmaId && (in_array('pmatree_edit', $GLOBALS['wgUser']->getRights()))){
        $impUrl = $GLOBALS['wgServer']."/en/Special:PmaTree?action=edit&parent_id={$pmaId}";
        $article->getContext()->getOutput()->addHTML(Self::addImplementationLink($impUrl));
      };
      if($top53_id)
      {
        // $article->getContext()->getOutput()->addWikiText($cat);
        foreach($categories as $cat)
        {
          $link = NULL;

          if(Self::compare($cat, 'Category:Problem level‏‎')){
            $link = 'http://top53.parallel.ru/algo_results/task/' . $top53_id;
          }elseif(Self::compare($cat, 'Category:Algorithm level‏‎')){
            $link = 'http://top53.parallel.ru/algo_results/algorithm/' . $top53_id;
          }
          if ($link){
            $article->getContext()->getOutput()->addHTML(self::linkTemplate($link));
          }
        }
      }
    }
  }
  public static function compare($asci,$utf)
  {
    for ($i=0;$i < strlen($asci);$i++)
    {
      if(substr($asci,$i,1) != mb_substr($utf,$i,1))
      {
        return false;
      }
    }
    return true;
  }
}
