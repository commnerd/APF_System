<?php

namespace System\Components\Validation\Rules;

use System\Components\Validation\Rule;

class Same extends Rule {
	const MESSAGE = "Two values must match.";

	public function validate($value) {
		if($value != $this->requestVals[$this->params[0]]) {
			return false;
		}

		return true;
	}
}