<?php
class PmaTreeHooks{
  public static function addUndescores($arg) {
    return str_replace(" ", "_", $arg);
  }
  public static function onLoadExtensionSchemaUpdates( DatabaseUpdater $updater ) {
    $updater->addExtensionTable( 'pma_tree_sync',
  		__DIR__ . '/sql/pma_tree_sync_table_create_table.sql' );
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
          if($cat == 'Category:Problem level‏‎'){
            $link = 'http://top53.parallel.ru/algo_results/task/' . $top53_id;
          }elseif($cat == 'Category:Algorithm level‏‎'){
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


  public static function onArticleViewHeader( &$article, &$outputDone, &$pcache ) {
    $id = $article -> getId();
    $categories = $article->getCategories();
    if($GLOBALS['wgSitename'] == 'Алговики')
    {
      $top53_id = Sync::Top53IdByRuPageId($id);
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
      if($top53_id)
      {
        // $article->getContext()->getOutput()->addWikiText($cat);
        foreach($categories as $cat)
        {
          $link = NULL;
          if($cat == 'Category:Problem level‏‎'){
            $link = 'http://top53.parallel.ru/algo_results/task/' . $top53_id;
          }elseif($cat == 'Category:Algorithm level‏‎'){
            $link = 'http://top53.parallel.ru/algo_results/algorithm/' . $top53_id;
          }
          if ($link){
            $article->getContext()->getOutput()->addHTML(self::linkTemplate($link));
          }
        }
      }
    }
  }
}
