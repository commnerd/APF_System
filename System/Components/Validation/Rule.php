<?php

namespace System\Components\Validation;

use System\Components\AppComponent;
use System\Components\DbConnection;

abstract class Rule extends AppComponent {

	protected $requestVals;
	protected $params;
	protected $extras;
	protected $db;

	public function __construct(array $requestVals, array $params = array(), DbConnection $db = null)
	{
		$this->requestVals = $requestVals;
		$this->params = $params;
		$this->extras = array();
		$this->db = $db;
	}

	abstract public function validate($value);

	public function getMessageExtras()
	{
		return $this->extras;
	}
}