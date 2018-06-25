<?php
$basePath = getenv( 'MW_INSTALL_PATH' ) !== false ? getenv( 'MW_INSTALL_PATH' ) : '/var/www/algowiki/ru' ;
require_once $basePath . '/maintenance/Maintenance.php';
class PmaUpdater extends Maintenance {

	public function execute() {

		Aricle::newFromTitle
	}
}
$maintClass = PmaUpdater::class;
require_once RUN_MAINTENANCE_IF_MAIN;
