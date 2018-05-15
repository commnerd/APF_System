<?php

namespace System\Components;

use System\Services\DirectoryScanner;

/**
 * Configuration reader utility
 */
class RouteReader
{
	/**
	 * Error to throw when path not set.
	 */
	const EXCEPTION_NULL_PATH = "Path not set, cannot read routes.";

	/**
	 * Configs read in
	 *
	 * @var array
	 */
	private $_routes = array();

	/**
	 * Static function to read in configs
	 *
	 * @return array                                   Associative array of variables and values
	 */
	public static function read($context = null)
	{
		$reader = new RouterReader($context);
		$reader->readRoutes();
	}

	/**
	 * Read in the configs
	 *
	 * @param  string $dir The path to read configs from
	 * @return void
	 */
	public function readConfigs($dir = null)
	{
		if(empty($dir)) {
			$dir = $this->app->config->get("path.routes");
		}
		if(empty($dir)) {
			throw ErrorException(self::EXCEPTION_NULL_PATH);
		}
		$files = DirectoryScanner::getFiles($dir);
		foreach($files as $file) {
			$extension = end(explode(".", $file));
			if($extension === 'php') {
				require_once($file);
			}
		}
	}
}
