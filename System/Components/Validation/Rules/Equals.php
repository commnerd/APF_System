<?php

namespace System\Components\Validation\Rules;

use System\Components\Validation\Rule;

class Equals extends Rule {
	const MESSAGE = "This value must equal \"%s\".";

	public function validate($value) {
		$this->extras[] = $this->params[0];
		return $value == $this->params[0];
	}
}