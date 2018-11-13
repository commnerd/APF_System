<?php

namespace System\Components;

use System\Exceptions\ValidationException;
use System\Components\DbConnection;
use System\Components\Router;

class Validator extends AppComponent
{
	public $ruleMap = array(
		'required' => 'Required',
		'required_if' => 'RequiredIf',
		'required_if_empty' => 'RequiredIfEmpty',
		'in' => 'In',
		'max' => 'Max',
		'min' => 'Min',
		'unique' => 'Unique',
		'numeric' => 'Numeric',
		'equals' => 'Equals',
		'not_equals' => 'NotEquals',
		'check_group' => 'CheckGroup',
		'same' => 'Same',
		'exists' => 'Exists',
	);

	protected $overrides = array();

	private $_db = null;

	private $_router = null;

	/**
	 * Build a validator
	 */
	public function __construct()
	{
		// Set overrides if they're defined
		if(isset($GLOBALS['errorOverrides'])) {
			$this->overrides = $GLOBALS['errorOverrides'];
		}

		// Autoset router and db references
		foreach($GLOBALS as $key => $val) {
			if($val instanceof Router) {
				$this->_router = $val;
			}
			if($val instanceof DbConnection) {
				$this->_db = $val;
			}
		}
	}

	public function validate(array $request, array $rules)
	{
		$errors = array();

		foreach($rules as $key => $ruleSet) {
			$rulesAry = explode('|', $ruleSet);

			foreach($rulesAry as $rule) {
				$triageAry = explode(':', $rule);
				$class = '\\System\\Components\\Validation\\Rules\\'.$this->ruleMap[$triageAry[0]];
				$params = array();
				if(sizeof($triageAry) > 1) {
					$params = explode(',', $triageAry[1]);
				}
				$obj = new $class($request, $params, $this->_db);
				if(!isset($request[$key])) {
					$request[$key] = null;
				}
				if(!$obj->validate($request[$key])) {
					if(!isset($errors[$key])) {
						$errors[$key] = array();
					}
					$msg = $this->_setMessage($key, $triageAry[0], $class, $obj);
					if(!empty($msg)) {
						$errors[$key][$triageAry[0]] = $msg;
					}
				}
			}

			if(empty($errors[$key])) {
				unset($errors[$key]);
			}
		}

		if(!empty($errors)) {
			throw new ValidationException('Your submission failed.', $errors);
		}
	}

	private function _setMessage($key, $rule, $class, $obj)
	{
		$msg = vsprintf($class::MESSAGE, $obj->getMessageExtras());

		$override = $this->_mapError($key, $rule);

		if(!is_null($override)) {
			return $override;
		}

		return $msg;
	}

	private function _mapError($key, $rule)
	{
		$msg = null;

		if(empty($this->overrides)) {
			return null;
		}

		if(
			isset($this->overrides[$key]) &&
			isset($this->overrides[$key][$rule])) {
			$msg = $this->overrides[$key][$rule];
		}

		$routeDef = $this->_router->match();
		if(
			isset($this->overrides['PAGES']) && 
			isset($this->overrides['PAGES'][$routeDef['name']]) &&
			isset($this->overrides['PAGES'][$routeDef['name']][$key]) &&
			isset($this->overrides['PAGES'][$routeDef['name']][$key][$rule])) {
			$msg = $this->overrides['PAGES'][$routeDef['name']][$key][$rule];
		}

		return $msg;
	}


}