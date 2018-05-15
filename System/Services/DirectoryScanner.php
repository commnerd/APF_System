<?php

namespace System\Services;

/**
 * Service for scanning directories
 */
class DirectoryScanner extends BaseService
{
	const EXCEPTION_PATH_NOT_SET = "You did not pass a directory.";

	const EXCEPTION_PATH_NOT_FOUND = "The path to the given directory does not exist.";

	const EXCEPTION_PATH_NOT_DIRECTORY = "The defined path is not a directory.";

	private function _checkPathSet($path)
	{
		if(empty($path)) {
			throw new \ErrorException(DirectoryScanner::EXCEPTION_PATH_NOT_SET);
		}
	}

	private function _checkPath($path)
	{
		if(!file_exists($path)) {
			throw new \ErrorException(DirectoryScanner::EXCEPTION_PATH_NOT_FOUND);
		}
	}

	private function _checkDirectory($path)
	{
		if(!is_dir($path)) {
			throw new \ErrorException(DirectoryScanner::EXCEPTION_PATH_NOT_DIRECTORY);
		}
	}

	protected function ___getFiles($path)
	{
		$contents = $this->_getDirContents($path);
		$files = array();
		foreach($contents as $inode) {
			$inode = $path.DIRECTORY_SEPARATOR.$inode;
			if(is_file($inode)) {
				array_push($files, $inode);
			}
		}

		return $files;
	}

	protected function ___getDirs($path)
	{
		$contents = $this->_getDirContents($path);
		$excludes = array('.', '..');
		$dirs = array();
		foreach($contents as $inode) {
			$inode = $path.DIRECTORY_SEPARATOR.$inode;
			if(is_dir($inode) && !in_array($inode, $excludes)) {
				array_push($files, $path.DIRECTORY_SEPARATOR.$inode);
			}
		}

		return $dirs;
	}

	private function _getDirContents($path) {
		$this->_checkPathSet($path);
		$this->_checkPath($path);
		$this->_checkDirectory($path);

		return scandir($path);
	}
}

?>
