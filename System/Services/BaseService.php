<?php 

namespace System\Services;

abstract class BaseService
{

	/**
	 * Try calling static method by instantiating class and running it
	 * @param  string $method The method name
	 * @param  array  $params The passed parameters
	 * @return mixed          The calculated value
	 */
	public static function __callStatic($method, $params)
	{
		$class = get_called_class();
		$service = new $class();
		return call_user_func_array(array($service, $method), $params);
	}

	/**
	 * Try calling public/protected method by prepending ___ to method name
	 * @param  string $method The method name
	 * @param  array  $params The passed parameters
	 * @return mixed          The calculated value
	 */
	public function __call($method, $params)
	{
		$method = "___".$method;
		if(method_exists($this, $method)) {
			return call_user_func_array(array($this, $method), $params);
		}
	}
}