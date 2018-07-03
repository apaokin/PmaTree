<?php
class ApiQueryPmaTree extends ApiQueryBase {
	public function __construct( $query, $moduleName ) {
		parent::__construct( $query, $moduleName, 'pma' );
	}
	public function execute() {
		// global $wgCheckUserForceSummary;
		$result = $this->getResult();
		$pmas = [];
		foreach(Pma::selectAllWithCategories() as $pma){
			$pma->type = Pma::$type_maps[$pma->type];
			$pmas[]=$pma;
		}
		$result->addValue( ['query', $this->getModuleName() ], 'results', $pmas );
	}
}
