<?php

namespace System\Components\Validation\Rules;

use System\Components\Validation\Rule;

class Numeric extends Rule {

	const MESSAGE = "Must be numeric.";

	public function validate($value)
	{
		return is_numeric($value);
	}
}