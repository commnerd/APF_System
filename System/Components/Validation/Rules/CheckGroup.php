<?php

namespace System\Components\Validation\Rules;

use System\Components\Validation\Rule;

class CheckGroup extends Rule {
	const MESSAGE = "At least one must be set.";
	
	public function validate($value) {
		if(empty($this->params)) {
			throw new \Exception("Improper use of required_if rule.");
		}

		foreach($this->params as $key) {
			if(isset($this->requestVals[$key]) && !empty($this->requestVals[$key])) {
				return true;
			}
		}
		return false;
	}
}