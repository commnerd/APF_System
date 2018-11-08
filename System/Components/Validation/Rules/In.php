<?php

namespace System\Components\Validation\Rules;

use System\Components\Validation\Rule;

class In extends Rule {

	const MESSAGE = "This is not a valid value.";

	public function validate($value)
	{
		return in_array($value, $this->params);
	}
}