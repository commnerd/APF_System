<?php

namespace System\Components\Validation\Rules;

use System\Components\Validation\Rule;

class Required extends Rule {
	const MESSAGE = "This is a required value.";

	public function validate($value) {
		if(is_string($value) && $value !== "") {
			return true;
		}

		if(empty($value)) {
			return false;
		}

		return true;
	}
}