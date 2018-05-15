<?php

namespace System\Interfaces;

interface TemplateSystemDriver
{
	public function render($template, array $params = array());
}
