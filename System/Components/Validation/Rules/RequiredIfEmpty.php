<?php

namespace System\Components\Validation\Rules;

use System\Components\Validation\Rules\Required;
use System\Components\Validation\Rule;

class RequiredIfEmpty extends Rule {
	const MESSAGE = "This field is required.";
	
	public function validate($value) {
		if(empty($this->params)) {
			throw new \Exception("Improper use of required_if_empty rule.");
		}

		$required = new Required($this->requestVals, $this->params, $this->db);

		if($required->validate($value)) {
			return true;
		}

		foreach($this->params as $key) {
			if(isset($this->requestVals[$key]) && $required->validate($this->requestVals[$key])) {
				return true;
			}
		}

		return false;
	}
}