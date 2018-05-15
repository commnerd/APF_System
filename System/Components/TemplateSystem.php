<?php

namespace System\Components;

use System\Interfaces\TemplateSystem as TemplateSystemInterface;
use System\Components\Templating\TwigDriver;

class TemplateSystem extends AppComponent implements TemplateSystemInterface
{
	/**
	 * System-wide name for twig templating system
	 *
	 * @var string
	 */
	const SYSTEM_TWIG = "twig";

	/**
	 * The templating system to use in rendering response
	 *
	 * @var string
	 */
	private $_system;

	/**
	 * The driver to use in rendering responses
	 *
	 * @var \System\Components\Templating\Driver
	 */
	private $_driver;

	/**
	 * The css includes
	 *
	 * @var array
	 */
	private $_cssFiles = array(
		"main.css",
	);

	/**
	 * The js includes
	 *
	 * @var array
	 */
	private $_jsFiles = array(
		"main.js",
	);

	/**
	 * The base directory for the js files
	 *
	 * @var string
	 */
	private $_jsBaseDir = "";

	/**
	 * The base directory for the js files
	 *
	 * @var string
	 */
	private $_cssBaseDir = "";

	/**
	 * The compiled css path for the template
	 *
	 * @var array
	 */
	private $_cssCompiledPath = "";

	/**
	 * The compiled js path for the template
	 *
	 * @var array
	 */
	private $_jsCompiledPath = "";


	/**
	 * Build the templating system
	 */
	public function __construct()
	{
		parent::__construct();

		$this->_system = $this->app->config->get('templating.system');

		$this->_cssBaseDir = $this->app->getBaseDir().
			DIRECTORY_SEPARATOR.
			$this->app->config->get('templating.path.css');
		$this->_jsBaseDir = $this->app->getBaseDir().
			DIRECTORY_SEPARATOR.
			$this->app->config->get('templating.path.js');
		switch($this) {
			case self::SYSTEM_TWIG:
			default:
				$this->_driver = new TwigDriver($this);
		}
	}

	/**
	 * Add CSS file to array
	 *
	 * @param  string $path Path to the CSS file (relative paths start at config location)
	 * @return void
	 */
	public function registerCssFile($path) {
		$this->_registerFile("css", $path);
	}

	/**
	 * Add JS file to array
	 *
	 * @param  string $path Path to the JS file (relative paths start at config location)
	 * @return void
	 */
	public function registerJsFile($path) {
		$this->_registerFile("js", $path);
	}

	/**
	 * Get the CSS
	 *
	 * @return string The CSS
	 */
	public function getCss() {
		return $this->_getContents("css");
	}

	/**
	 * Get the JS
	 *
	 * @return string The JS
	 */
	public function getJs() {
		return $this->_getContents("js");
	}

	/**
	 * Get the servable compiled JS path
	 *
	 * @return string The servable file path
	 */
	public function getJsCompiledPath()
	{
		return $this->_getCompiledPath("js");
	}

	/**
	 * Get the servable compiled CSS path
	 *
	 * @return string The servable file path
	 */
	public function getCssCompiledPath()
	{
		return $this->_getCompiledPath("css");
	}

	/**
	 * Render the template
	 *
	 * @param  string $template The relative path to the route to render
	 * @param  array  $params   The params to pass to the template
	 * @return string           The HTML/JSON to pass to the client
	 */
	public function render($template, array $params)
	{
		$this->_cssCompiledPath = $this->_generateAndWriteToPath("css");
		$this->_jsCompiledPath = $this->_generateAndWriteToPath("js");

		return $this->_driver->render($template, $params);
	}

	/**
	 * Get the servable compiled path by context
	 *
	 * @return string The servable file path
	 */
	private function _getCompiledPath($context) {
		return "/$context/".$this->{"_".$context."CompiledPath"};
	}

	/**
	 * Determine unique md5 name for file
	 *
	 * @param  string $context "css" or "js"
	 * @return string          Filename for file
	 */
	private function _generateAndWriteToPath($context) {
		$writePath = $this->app->getBaseDir().DIRECTORY_SEPARATOR.$this->app->config->get('path.'.$context);
		$content = $this->{"get".ucfirst($context)}();
		$fileName = md5($content).".".$context;
		file_put_contents($writePath.DIRECTORY_SEPARATOR.$fileName, $content);
		return $fileName;
	}

	/**
	 * Get file contents based on context
	 *
	 * @param  string $context "js" or "css"
	 * @return void
	 */
	private function _getContents($context) {
		$baseDir = $this->{"_".$context."BaseDir"};
		$contents = "";
		$list = $this->{"_".$context."Files"};
		foreach($list as $file) {
			while(substr($baseDir, -1) === DIRECTORY_SEPARATOR) {
				$baseDir = substr($baseDir, 0, -1);
			}
			while(substr($file, 0, 1) === DIRECTORY_SEPARATOR) {
				$file = substr($file, 1);
			}
			$contents .= file_get_contents($baseDir.DIRECTORY_SEPARATOR.$file);
			$contents .= "\n";
		}
		return $contents;
	}


	/**
	 * Register a file based on context
	 * @param  string $context "js" or "css"
	 * @param  string $path    Passed path to the file
	 * @return void
	 */
	private function _registerFile($context, $path) {
		$this->{"_".$context."Files"}[] = $path;
	}
}
