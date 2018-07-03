z<?php
$basePath = getenv( 'MW_INSTALL_PATH' ) !== false ? getenv( 'MW_INSTALL_PATH' ) : '/var/www/algowiki/ru' ;
require_once $basePath . '/maintenance/Maintenance.php';
class PmaUpdater extends Maintenance {

	public function addUndescores($arg) {
		return str_replace(" ", "_", $arg);
	}


	public function execute() {

		Sync::truncateTable();
		$row = 1;

		if (($handle = fopen(__DIR__ . "/PmaTreeSync.csv", "r")) !== FALSE) {
				fgetcsv($handle, 1000, ",");
				while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
						Sync::addRow($this->addUndescores($data[0]), $this->addUndescores($data[1]),$data[2]);
				}
				fclose($handle);
		}
	}

}
$maintClass = PmaUpdater::class;
require_once RUN_MAINTENANCE_IF_MAIN;
