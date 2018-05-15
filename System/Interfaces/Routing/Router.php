<?php

namespace System\Interfaces\Routing;

/**
 * The definition of a Router in this system
 */
interface Router {
	/**
	 * Map routes to their appropriate controllers
	 * @param  string         $verb       Pip-delimited verbs (ie GET, POST, PUT, DELETE, HEAD, PATCH)
	 * @param  string         $path       The relative path to respond to
	 * @param  string|Closure $controller A map to the appropriate code
	 * @param  string         $name       The route name
	 * @return void
	 */
	public function map($verb, $path, $controller, $name);

	/**
	 * Match a route with its controller/method
	 * @param  string $requestUrl      The URL to match
	 * @param  string $requestMethod   The Verb to match
	 * @return System\Interfaces\Route Route object
	 */
	public function match($requestUrl = null, $requestMethod = null);
}