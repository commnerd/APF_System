<?php

namespace System\Interfaces\Routing;

/**
 * The definition of a Router in this system
 */
interface Route {
	/**
	 * Get the target
	 * 
	 * @return string Target controller/method
	 */
	public function getTarget();

	/**
	 * Set the target
	 * 
	 * @param string $target Target controller/method
	 */
	public function setTarget($target);

	/**
	 * Get the params for the target
	 * 
	 * @return array The parameters to pass to the target
	 */
	public function getParams();

	/**
	 * Set the params for the target
	 * 
	 * @param array $params The parameters to pass to the target
	 */
	public function setParams($params);

	/**
	 * Get the route name
	 * 
	 * @return string Route name
	 */
	public function getName();

	/**
	 * Set the route name
	 * 
	 * @param string $name Route name
	 */
	public function setName($name);
}