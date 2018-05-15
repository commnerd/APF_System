<?php 

namespace CONSAPP\Services;

use Upload\Validation\Mimetype;
use Upload\Storage\FileSystem;
use CONSAPP\Entities\Request;
use Upload\Validation\Size;
use Upload\File;

class UploadHandler
{
	/**
	 * File not uploaded error
	 *
	 * @var string
	 */
	const ERROR_FILE_NOT_UPLOADED =  "No file was uploaded.";

	/**
	 * Error to show in place of DEFAULT_MIMETYPE_ERROR listed below
	 *
	 * @var string
	 */
	const ERROR_INVALID_MIMETYPE = "Invalid File type.";

	/**
	 * Invalid mimetype default error string
	 *
	 * @var string
	 */
	const DEFAULT_MIMETYPE_ERROR = "Invalid mimetype";

	/**
	 * The key in the config used to access the upload destination setting
	 *
	 * @var string
	 */
	const CONFIG_UPLOAD_DESTINATION_KEY = 'upload_destination';

	/**
	 * The request definition
	 * 
	 * @var Request
	 */
	private $_request;

	/**
	 * The filesystem destination for the files
	 * 
	 * @var FileSystem
	 */
	private $_storage;

	/**
	 * Prepend this string to the file names based on key
	 * 
	 * @var string
	 */
	private $_name_prefix = "";

	/**
	 * Append this string to the file names based on key
	 * 
	 * @var string
	 */
	private $_name_suffix = "";

	/**
	 * Callback used to distinguish filename
	 *
	 * \Closure $callback
	 */
	private $_nameCallback = null;

	/**
	 * Successfully uploaded files
	 * 
	 * @var array
	 */
	private $_successes = array();

	/**
	 * The errors spit back by the upload proces
	 * 
	 * @var array
	 */
	private $_errors = array();

	/**
	 * Validation definitions
	 *
	 * @var array (double array ["$key" => array(validation 1, validation 2)])
	 */
	private $_validations = array();

	/**
	 * Static method for calling processor
	 * 
	 * @return array Successful uploads and errors array
	 */
	public static function process(array $validations = null)
	{
		$handler = new UploadHandler($validations);
		$handler->processRequest();
		return array(
			'successes' => $handler->getSuccesses(),
			'errors' => $handler->getErrors()
		);
	}

	/**
	 * UploadHandler constructor
	 * 
	 * @param array $validations (double array ["$key" => array(validation 1, validation 2)])
	 */
	public function __construct(array $validations = null)
	{
		$this->_request = new Request();
		$this->_storage = new UploadHandlerFileSystem($this->_getUploadDestination());
		if(isset($validations)) {
			$this->_validations = $validations;
		}
	}

	/**
	 * The meat of the class, read in and collect information about the uploads
	 * 
	 * @return void
	 */
	public function processUploads()
	{
		$files = $this->_request->getFiles();
		if(!empty($files)) {
			foreach($files as $key => $value) {
				if($files[$key]["error"] == 0) {
					$intendedFilename = $this->_name_prefix.$key.$this->_name_suffix; 
					if(isset($this->_nameCallback)) {
						$intendedFilename = call_user_func_array($this->_nameCallback, array($key));
						dump($intendedFilename);
					}
                    $file = new \Upload\File($key, $this->_storage);
                    $file->setName($intendedFilename);
                    $file->addValidations($this->_validations[$key]);
                    // Try to upload file
                    try {
                        // Success!
                        $file->upload();
                        $this->_successes[$key] = array(
                        	'key'        => $key,
                            'name'       => $file->getNameWithExtension(),
                            'file'       => $file,
                        );
                    } catch (\Exception $e) {
                        // Fail!
                        $this->_errors[$key] = $file->getErrors();
                        $this->_errors[$key][0] = str_replace(
                        	self::DEFAULT_MIMETYPE_ERROR,
                        	self::ERROR_INVALID_MIMETYPE,
                        	$this->_errors[$key][0]
                        );
                    }
                } else {
                    $this->_errors[$key][0] = self::ERROR_FILE_NOT_UPLOADED;
                }
			}
		}
	}

	/**
	 * Set callback used for determining filename
	 * 
	 * @param \Closure $callback Callback used to get filename
	 */
	public function setNameCallback(\Closure $callback) {
		$this->_nameCallback = $callback;
	}

	/**
	 * Set filename prefix variable
	 * 
	 * @param string $prefix String to prepend to filename
	 */
	public function setFilenamePrefix($prefix)
	{
		$this->_name_prefix = $prefix;
	}

	/**
	 * Get filename prefix variable
	 * 
	 * @return string String to prepend to filename
	 */
	public function getFilenamePrefix()
	{
		return $this->_name_prefix;
	}

	/**
	 * Set filename suffix variable
	 * 
	 * @param string $suffix String to append to filename
	 */
	public function setFilenameSuffix($suffix)
	{
		$this->_name_prefix = $prefix;
	}

	/**
	 * Get filename suffix variable
	 * 
	 * @return string String to append to filename
	 */
	public function getFilenameSuffix()
	{
		return $this->_name_suffix;
	}

	/**
	 * Get the errors generated by processing the uploads
	 * 
	 * @return array The array of errors
	 */
	public function getErrors()
	{
		return $this->_errors;
	}

	/**
	 * Get the success data from successfully uploaded files
	 * 
	 * @return array Successfully uploaded file data array
	 */
	public function getSuccesses()
	{
		return $this->_successes;
	}

	/**
	 * Set the overwrite boolean to force file overwriting
	 * 
	 * @param boolean $overwrite True = force overwrite, False = throw error
	 */
	public function setOverwrite($overwrite) {
		$this->_storage->setOverwrite($overwrite);
	}

	/**
	 * Get the destination path for the uploads
	 * 
	 * @return string The path for the uploads
	 */
	private function _getUploadDestination()
	{
		GLOBAL $configArr;
		return $configArr[self::CONFIG_UPLOAD_DESTINATION_KEY];
	}
}

class UploadHandlerFileSystem extends \Upload\Storage\FileSystem
{
	/**
	 * Force overwrite of file
	 * 
	 * @param bool $overwrite Overwrite file (true = overwrite, false = throw error)
	 */
	public function setOverwrite($overwrite) {
		$this->overwrite = $overwrite;
	}
}

?>