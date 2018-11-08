<?php

namespace System\Components\Validation\Rules;

use System\Components\Validation\Rule;

class Min extends Rule {

	const MESSAGE = "This must not excede %s.";

	public function validate($value)
	{
		if(is_numeric($value) && $value < $this->params[0]) {
			$this->extras[] = $this->params[0];
			return false;
		}

		if(is_string($value) && strlen($value) < $this->params[0]) {
			$this->extras[] = $this->params[0]." characters";
			return false;
		}

		return true;
	}
}