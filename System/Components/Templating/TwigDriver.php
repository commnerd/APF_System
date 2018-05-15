<?php

namespace System\Components\Templating;

use System\Interfaces\TemplateSystemDriver;
use System\Interfaces\TemplateSystem;
use System\Components\AppComponent;
use System\Components\Model;
use Twig_Loader_Filesystem;
use Twig_SimpleFunction;
use Twig_Environment;

class TwigDriver extends AppComponent implements TemplateSystemDriver
{
	/**
	 * The system used to render templates
	 *
	 * @var string
	 */
	private $_system;

	/**
	 * The twig environment used for rendering
	 *
	 * @var Twig_Environment
	 */
	private $_env;

	/**
	 * build the template system driver
	 *
	 * @param TemplateSystem $system The driver used to build the system
	 */
	public function __construct(TemplateSystem $system)
	{
		parent::__construct();
		$config = $this->app->config;
		$loader = new Twig_Loader_Filesystem($config->get('templating.paths'));
		$this->_system = $system;
		$this->_env = new Twig_Environment($loader, array(
				// 'cache' => $config->get('templating.path.cache'),
		));
		$this->registerTwigFunctions();
	}

	/**
	 * Render the template with its bound data
	 *
	 * @param  string $template Relative path to the template to render
	 * @param  array  $params   Parameters to pass to the renderer
	 * @return string           The rendered template
	 */
	public function render($template, array $params = array())
	{
		foreach($params as $paramIndex => $param) {
			if($param instanceof Model) {
				$params[$paramIndex] = $param->toArray();
			}
			if(is_array($param)) {
				$params[$paramIndex] = $this->_cascadeToArray($param);
			}
		}
		return $this->_env->render($template, $params);
	}

	/**
	 * Cascade down array chains looking for models to cast to arrays
	 *
	 * @param  iterable $array Arrays full of potential models
	 * @return array               Arrays of converted Models
	 */
	private function _cascadeToArray(iterable $array) {
		foreach($array as $index => $value) {
			if(is_array($value)) {
				$array[$index] = $this->_cascadeToArray($value);
			}
			if($value instanceof Model) {
				$array[$index] = $value->toArray();
			}
		}
		return $array;
	}

	/**
	 * Register functions to use within twig
	 *
	 * @return void
	 */
	private function registerTwigFunctions()
	{
		$app = $this->app;
		$driverContext = $this;

		$functions = array(
			new Twig_SimpleFunction('route', function(string $name, array $params = array()) use ($app) {
				$route = $app->router->generate($name, $params);
				return $route;
			}),
			new Twig_SimpleFunction('css', function() use ($driverContext) {
				return $driverContext->_system->getCssCompiledPath();
			}),
			new Twig_SimpleFunction('js', function() use ($driverContext) {
				return $driverContext->_system->getJsCompiledPath();
			}),
		);

		foreach($functions as $function) {
			$this->_env->addFunction($function);
		}
	}
}
