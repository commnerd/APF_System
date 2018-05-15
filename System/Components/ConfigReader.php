<?php

namespace System\Components;

use System\Services\DirectoryScanner;

/**
 * Configuration reader utility
 */
class ConfigReader extends AppComponent
{
	/**
	 * Error to throw when path not set.
	 */
	const EXCEPTION_NULL_PATH = "Path not set, cannot read configs.";

	/**
	 * Default config route
	 */

	/**
	 * Configs read in
	 *
	 * @var array
	 */
	private $_configs = array();

	/**
	 * Read in the configs
	 *
	 * @param  string $dir The path to read configs from
	 * @return void
	 */
	public function readConfigs($dir = null)
	{
		if(empty($dir)) {
			$dir = $this->app->config->get("path.config");
		}
		if(empty($dir)) {
			throw new \ErrorException(self::EXCEPTION_NULL_PATH);
		}
		$files = DirectoryScanner::getFiles($dir);
		foreach($files as $file) {
			$fileInfo = pathinfo($file);
			if($fileInfo['extension'] === 'ini') {
				$configs = parse_ini_file($file);
				if($fileInfo['filename'] !== "global") {
					$configs = $this->_rekeyConfigs($configs, $fileInfo['filename']);
				}
				$this->_configs = array_merge($this->_configs, $configs);
			}
		}
	}

	/**
	 * Get the read-in configs
	 *
	 * @return array  The read-in configs
	 */
	public function getConfigs()
	{
		return $this->_configs;
	}

	/**
	 * Rekey the configs by prefixing with base namespace
	 * @param  array  $configs Configuration array
	 * @param  string $base    Prefixing namespace
	 * @return array           Rekeyed configs array
	 */
	private function _rekeyConfigs($configs, $base)
	{
		$rekeyed = array();
		foreach($configs as $key => $val) {
			$rekeyed[$base.'.'.$key] = $val;
		}
		return $rekeyed;
	}

}
