<?php

namespace System\Exceptions;

class ValidationException extends \Exception
{
	private $_errors;

	public function __construct($message, array $errors)
	{
		$this->_errors = $errors;
	}

	public function getErrors()
	{
		return $this->_errors;
	}
}