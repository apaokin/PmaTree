<?php
class PmaTreeHooks{
  var $dbr;
  var $counter;
	var $counterEn;
	var $output;

  public static function linkTemplate($link){
    return '<a style="float: right;" href="' . $link . '">
    <span style="font-size:10px;" class="mw-ui-button mw-ui-progressive">Get perf. data</span></a>';
  }

  public static function onArticleViewHeader( &$article, &$outputDone, &$pcache ) {
    $id = $article -> getId();
    if($GLOBALS['wgSitename'] == 'Алговики')
    {
      if($id == 291){//55
        $link = 'http://top53.parallel.ru/algo_results/task/29';
      }elseif($id == 294){ //99
        $link = 'http://top53.parallel.ru/algo_results/algorithm/5';
      }elseif($id == 296){ //100
        $link = 'http://top53.parallel.ru/algo_results/algorithm/6';
      }elseif($id == 304){ //263
        $link = 'http://top53.parallel.ru/algo_results/algorithm/2';
      }else{
        return;
      }
    }
    else{
      if($id == 55){
        $link = 'http://top53.parallel.ru/algo_results/task/29';
      }elseif($id == 99){
        $link = 'http://top53.parallel.ru/algo_results/algorithm/5';
      }elseif($id == 100){
        $link = 'http://top53.parallel.ru/algo_results/algorithm/6';
      }elseif($id == 263){
        $link = 'http://top53.parallel.ru/algo_results/algorithm/2';
      }else{
        return;
      }
    }
    $article->getContext()->getOutput()->addHTML(self::linkTemplate($link));
  }
}
