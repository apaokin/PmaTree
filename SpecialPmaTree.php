<?php
class SpecialPmaTree extends SpecialPage {
  var $dbr;
  var $counter;
	var $counterEn;
	var $output;

  public static function linkTemplate($link){
    return '<a rel="nofollow" class="external text" href="' . $link . '">
    <span style="font-size:10px;" class="mw-ui-button mw-ui-progressive">Get perf. data</span></a>';
  }

  public static function onArticleViewHeader( &$article, &$outputDone, &$pcache ) {
    $article->getContext()->getOutput()->addHTML(self::linkTemplate('http://top53.parallel.ru/algo_results/algorithm/5'));
  }

  function __construct() {
    parent::__construct( 'PmaTree' );
  }

  function getGroupName() {
  	return 'wiki';
  }

  function execute( $par ) {
    $request = $this->getRequest();
    $this->setHeaders();
		$this ->dbr = wfGetDB( DB_SLAVE);
    $this ->dbrEn = wfGetDB( DB_SLAVE,[],'algowiki_en' );
		$this->getOutput()->addWikiText('dddddd');

  }
}
