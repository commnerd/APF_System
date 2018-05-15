<?php

namespace System;

use System\Interfaces\App as AppInterface;
use System\Services\DirectoryScanner;
use System\Components\AppComponent;
use System\Components\ConfigReader;
use System\Components\DbConnection;
use System\Components\Response;
use System\Components\Config;
use System\Components\Router;
use System\Components\Route;
use ReflectionParameter;
use ReflectionClass;

/**
 * The overarching system object
 */
class App implements AppInterface
{
	/**
	 * Component mapping for the app
	 *
	 * @var array
	 */
	private $_componentMap = array();

	/**
	 * Component alias mappings for userspace use
	 *
	 * @var array
	 */
	private $_componentAliasMap = array();

	/**
	 * Constructor for Application; bootstrap config
	 */
	public function __construct()
	{
		$GLOBALS['app'] = $this;
		$configClass = "\System\Components\Config";
		$this->_componentAliasMap['config'] = $configClass;
		$this->_componentMap[$configClass] = new $configClass();
	}

	/**
	 * Getter for managed components
	 *
	 * @param  string $name The namespace and class name you want retrieved
	 * @return \System\Components\AppComponent The associated component
	 */
	public function __get($name)
	{
		if($name === "scalar") {
			return null;
		}

		if(!empty($this->_componentAliasMap[$name])) {
			$name = $this->_componentAliasMap[$name];
		}

		if(empty($this->_componentMap[$name])) {
			$this->_componentMap[$name] = new $name();
		}

		return $this->_componentMap[$name];
	}

	/**
	 * Get the application's base directory
	 *
	 * @return string The base directory for the application
	 */
	public function getBaseDir()
	{
		$pathArray = explode(DIRECTORY_SEPARATOR, getcwd());
		array_pop($pathArray);
		return implode(DIRECTORY_SEPARATOR, $pathArray);
	}

	/**
	 * Public accessor to initialize the app
	 *
	 * @return System\Models\App The application context
	 */
	public static function init()
	{
		$app = new App();
		$app->bootstrap();
		return $app;
	}

	/**
	 * Bootstrap system
	 *
	 * @return void
	 */
	public function bootstrap()
	{
		$this->_initSession();
		$this->_fillValuesForDumbQuery();
		$this->_setupRequest();
		$this->_loadConfigs();
		$this->_loadRoutes();
		$this->_loadDb();
		$this->_runMiddlewares();
		$route = $this->_getMappedRoute();
		$this->_loadResponse($route);
	}

	/**
	 * Intilize the session for this instance
	 *
	 * @return void
	 */
	public function _initSession()
	{
		$this->_mapComponent('session', "\System\Components\Session");
	}

	/**
	 * Returns the contents from a Viewable interface
	 *
	 * @return string
	 */
	public function sendResponse()
	{
		$response = $this->{'\System\Components\Response'};
		$type = $response->type;
		$params = array_merge($response->params, $this->config->get());
		switch($type) {
			case Response::TYPE_REDIRECT:
				header('Location: '.$response->route);
				http_response_code($response->code);
				break;
			default:
				echo $this->{'\System\Components\TemplateSystem'}->render($response->template, $params);
				break;
		}
	}

	public function registerComponent(AppComponent $obj)
	{
		$class = get_class($obj);
		if(!isset($this->{$class})) {
			$this->{$class} = $obj;
		}
	}

	private function _fillValuesForDumbQuery()
	{
		if(!isset($_SERVER['REQUEST_METHOD'])) {
			$_SERVER['REQUEST_METHOD'] = 'GET';
		}

		if(!isset($_SERVER['REQUEST_URI'])) {
			$_SERVER['REQUEST_URI'] = '/';
		}
	}

	/**
	 * Pull in configs
	 *
	 * @return void
	 */
	private function _loadConfigs()
	{

		$configReader = new ConfigReader();
		$configReader->readConfigs($this->getBaseDir().DIRECTORY_SEPARATOR."config");
		$this->config->setConfigs($configReader->getConfigs());
	}

	/**
	 * Setup the request for the application
	 *
	 * @return void
	 */
	private function _setupRequest()
	{
		$this->_mapComponent("request","\System\Components\Request");
	}

	/**
	 * Pull in configs
	 *
	 * @return void
	 */
	private function _loadRoutes()
	{
		$dir = $this->getBaseDir().DIRECTORY_SEPARATOR.$this->config->get('path.routes');
		$files = DirectoryScanner::getFiles($dir);
		$router = new Router($this);

		foreach($files as $file) {
			$router->addRoutes(include($file));
		}

		$this->_componentMap['\System\Components\Router'] = $router;
		$this->_componentAliasMap['router'] = '\System\Components\Router';
	}

	/**
	 * Load the database object
	 * @return void
	 */
	private function _loadDb()
	{
		$this->_componentMap['\System\Components\DbConnection'] =
			new DbConnection(
				$this->config->get('database.username'),
				$this->config->get('database.password'),
				$this->config->get('database.hostname'),
				$this->config->get('database.dbname'),
				$this->config->get('database.port'));
		$this->_componentAliasMap['database'] = '\System\Components\DbConnection';
	}

	/**
	 * Run middlewares
	 *
	 * @return void
	 */
	private function _runMiddlewares()
	{
		$dir = $this->config->get('path.src.middlewares');
		$files = DirectoryScanner::getFiles($this->getBaseDir().DIRECTORY_SEPARATOR.$dir);
		foreach($files as $file) {
			require_once($file);
		}
	}

	/**
	 * Get the mapped route
	 */
	private function _getMappedRoute()
	{
		return new Route($this->router->match(
			$this->request->getUrl(),
			$this->request->getMethod()
		));
	}

	/**
	 * Utility method used to quickly map components
	 * @param  string $name  The intended alias
	 * @param  string $class The fully qualified class name
	 * @return void
	 */
	private function _mapComponent($name, $class)
	{
		$this->_componentAliasMap[$name] = $class;
		$this->_componentMap[$class] = new $class();
	}

	/**
	 * Get the contents from the route
	 *
	 * @param  \System\Components\Route $route  The route definition
	 * @return string                           The call contents
	 */
	private function _loadResponse(Route $route)
	{
		$controller = $route->controller;
		$method = $route->method;
		$paramTypes = $this->_getParamTypes($controller, $method);
		$params = $this->_getParamValues($paramTypes);
		$obj = new $controller();
		$this->{"\System\Components\Response"} = call_user_func_array( array($obj, $method), $params );
	}

	/**
	 * Get the parameter types to pass to the method
	 * @param  string $controller The fully qualified name for the controller
	 * @param  string $method     The name of the method
	 * @return array              The list of variable types for the method
	 */
	private function _getParamTypes($controller, $method)
	{
		$class = new ReflectionClass($controller);

		$params = $class->getMethod($method)->getParameters();
		$paramTypes = array();

		foreach($params as $param) {
			if(!is_object($param->getClass())) {
				$paramTypes[] = "scalar";
			}
			else {
				$paramTypes[] = $param->getClass()->name;
			}
		}

		return $paramTypes;
	}

	private function _getParamValues($paramTypes) {
		$routeParams = $this->_getMappedRoute()->params;
		$params = array();
		foreach($paramTypes as $position => $type) {
			if($type === "scalar") {
				$params[] = array_shift($routeParams);
			}
			else {
				$params[] = $this->{$type};
			}
		}
		return $params;
	}
}
