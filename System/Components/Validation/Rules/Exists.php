<?php

namespace System\Components\Validation\Rules;

use System\Components\Validation\Rule;
use System\Components\DbConnection;
use System\Components\DbQuery;

class Exists extends Rule {

	const MESSAGE = "Does not exist.";

	public function validate($value)
	{
		if(is_null($this->db)) {
			throw new \Exception("No database declared to test against.");
		}
		if(sizeof($this->params) < 2) {
			throw new \Exception("Something is wrong with your validation formatting.");
		}

		$table = array_shift($this->params);
		$column = array_shift($this->params);

		$except = null;
		if(!empty($this->params)) {
			$except = array_shift($this->params);
		}

		$idColumn = "id";
		if(!empty($this->params)) {
			$idColumn = array_shift($this->params);
		}

		$vType = "s";
		
		$query = "SELECT COUNT(*) as `count` FROM `$table` WHERE `$column` = ?";
		if(is_numeric($value)) {
			$vType = "i";
		}
		$bindings = array($vType, $value);
		
		$query = new DbQuery(
			$query, $bindings
		);
		// exit(print_r($query, true));
		$result = $this->db->runQuery($query);

		$row = array_pop($result);
		return $row['count'] > 0;
	}
}