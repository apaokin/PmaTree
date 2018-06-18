<?php
class SpecialPmaTree extends SpecialPage {
  var $dbr;
  var $counter;
	var $counterEn;
	var $output;

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
