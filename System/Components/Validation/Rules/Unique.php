<?php

namespace System\Components\Validation\Rules;

use System\Components\Validation\Rule;
use System\Components\DbConnection;
use System\Components\DbQuery;

class Unique extends Rule {

	const MESSAGE = "Must be unique.";

	public function validate($value)
	{
		if(is_null($this->db)) {
			throw new \ErrorException("No database declared to test against.");
		}
		if(sizeof($this->params) < 2) {
			throw new \ErrorException("Something is wrong with your validation formatting.");
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

		if(!empty($except)) {
			$query .= " AND `$idColumn` != ?";
			$type = "s";
			if(is_numeric($except)) {
				$type = "i";
			}
			$bindings[0] .= $type;
			$bindings[] = $except;
		}

		if(sizeof($this->params) % 2 !== 0) {
			throw new \ErrorException("Something is wrong with your validation formatting.");
		}

		if(!empty($this->params)) {
			for($i = 0; $i < sizeof($this->params); $i += 2) {
				$query .= " AND `".$this->params[$i]."` = ?";
				$type = "s";
				if(is_numeric($this->params[$i+1])) {
					$type = "i";
				}
				$bindings[0] .= $type;
				$bindings[] = $this->params[$i+1];
			}
			
		}
		
		$query = new DbQuery(
			$query, $bindings
		);
		// exit(print_r($query, true));
		$result = $this->db->runQuery($query);

		$row = array_pop($result);
		return $row['count'] == 0;
	}
}