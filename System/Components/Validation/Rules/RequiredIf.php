<?php

namespace System\Components\Validation\Rules;

use System\Components\Validation\Rule;

class RequiredIf extends Rule {
	const MESSAGE = "This is required.";
	
	public function validate($value) {
		if(sizeof($this->params) % 2 > 0) {
			throw new \Exception("Improper use of required_if rule.");
		}
		if(empty($value)) {	
			for($i = 0; $i < sizeof($this->params); $i += 2) {
				if(isset($this->requestVals[$this->params[$i]]) && $this->requestVals[$this->params[$i]] == $this->params[$i + 1]) {
					return false;
				}
			}
		}
		return true;
	}
}