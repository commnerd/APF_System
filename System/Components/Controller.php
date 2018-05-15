<?php

namespace System\Components;

use Traversable;

class Controller extends AppComponent
{
	/**
	 * Reference to app's router for internal use
	 *
	 * @var \System\Components\Router
	 */
	private $_router;

	/**
	 * Construct Controller instance
	 */
	public function __construct()
	{
		parent::__construct();

		$this->_router = $this->app->router;
	}

	/**
	 * Return view for use in System\App
	 *
	 * @param  string                       $template The relative path of the template to be displayed
	 * @param  array                        $params   The array of parameters to pass to the template
	 * @return System\Components\Response             The response to be returned to the client
	 */
	public function view($template, array $params = array())
	{
		$params[Response::TYPE_TEMPLATE] = $template;

		return new Response($params);
	}

	/**
	 * Return view for use in System\App
	 *
	 * @param  string                       $name     The redirected route name for the client
	 * @param  array                        $params   Params for the redirect
	 * @return System\Components\Response             The response to be returned to the client
	 */
	public function redirect($name, array $params = array())
	{
		$route = $this->_getRedirectRoute($name);

		return new Response(array(
			'type' => Response::TYPE_REDIRECT,
			'route' => $route,
			'code' => 302,
		));
	}

	/**
	 * Generate from name, unless name is a keyword
	 * 
	 * @param  string $name Route name or keyword
	 * @return string       Path
	 */
	private function _getRedirectRoute($name)
	{
		$router = $this->_router;
		if(preg_match('/^(prev|back)$/', $name)) {
			return $_SERVER['HTTP_REFERER'];
		}
		return $router->generate($name, $params);
	}
}
