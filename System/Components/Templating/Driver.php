<?php

namespace System\Components\Templating;

use System\Components\AppComponent;
use System\Components\Model;
use Twig_Loader_Filesystem;
use Twig_SimpleFunction;
use Twig_Environment;

class Driver extends AppComponent
{
	private $_system;

	public function __construct($system)
	{
		parent::__construct();
		switch(strtolower($system)) {
			case 'twig':
			default:
			$config = $this->app->config;
			$loader = new Twig_Loader_Filesystem($config->get('templating.paths'));
			$this->_system = new Twig_Environment($loader, array(
				// 'cache' => $config->get('templating.cache.path'),
			));
			$this->registerTwigFunctions();
		}
	}

	public function render(string $template, array $params = array())
	{
		foreach($params as $paramIndex => $param) {
			if($param instanceof Model) {
				$params[$paramIndex] = $param->toArray();
			}
			if(is_array($param)) {
				foreach($param as $valueIndex => $value) {
					if($value instanceof Model) {
						$params[$paramIndex][$valueIndex] = $value->toArray();
					}
				}
			}
		}
		return $this->_system->render($template, $params);
	}

	private function registerTwigFunctions()
	{
		$app = $this->app;

		$functions = array(
			new Twig_SimpleFunction('route', function(string $name, array $params) use ($app) {
				$route = $app->router->generate($name, $params);
				return $route;
			})
		);

		foreach($functions as $function) {
			$this->_system->addFunction($function);
		}
	}
}
