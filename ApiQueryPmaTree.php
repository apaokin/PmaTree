<?php
class ApiQueryPmaTree extends ApiQueryBase {
	public function __construct( $query, $moduleName ) {
		parent::__construct( $query, $moduleName, 'pma' );
	}
	public function execute() {
		// global $wgCheckUserForceSummary;
		$result = $this->getResult();
		$result->addValue( ['query', $this->getModuleName() ], 'results', 'ddddd' );
	}
}
