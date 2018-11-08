<?php

namespace System\Components;

use System\Interfaces\Request as RequestInterface;
use System\Components\DbConnection;
use System\Components\Validator;

/**
 * Request object used to gather information about the request
 */
class Request extends AppComponent implements RequestInterface
{
	/**
	 * The request method (i.e. GET, POST, PUT, DELETE, HEAD, etc.)
	 *
	 * @var string
	 */
	private $_method;

	/**
	 * Array of request header
	 *
	 * @var array
	 */
	private $_headers;

	/**
	 * Arguments passed with request
	 *
	 * @var array
	 */
	private $_arguments;

	/**
	 * The URL being requested
	 *
	 * @var string
	 */
	private $_requestUrl;

	/**
	 * Constructor for a request
	 */
	public function __construct()
	{
		parent::__construct();

		$this->_method = $_SERVER['REQUEST_METHOD'];
		if(isset($_REQUEST['_method'])) {
			$this->_method = $_REQUEST['_method'];
			unset($_REQUEST['_method']);
		}

		$this->_headers = $_SERVER;

		$this->_arguments = $_REQUEST;

		$this->_requestUrl = $_SERVER['REQUEST_URI'];
	}

	public function __get($name)
	{
		if(!isset($this->_arguments[$name])) {
			return null;
		}
		return $this->_arguments[$name];
	}

	public function __set($name, $value)
	{
		$this->_arguments[$name] = $value;
	}

	/**
	 * Push arguments into request
	 * 
	 * @param string $var Variable key
	 * @param mixed  $val Variable value
	 */
	public function set($var, $val) {
		$this->_arguments[$var] = $val;
	}

	/**
	 * Retrieve header information
	 *
	 * @param  string $name The name of the header to retrieve
	 * @return string       The header value
	 */
	public function getHeader($name) {
		return $this->_headers['HTTP_'.strtoupper($name)];
	}

	/**
	 * Get the request method
	 *
	 * @return string The request method
	 */
	public function getMethod()
	{
		return $this->_method;
	}

	/**
	 * Get the current URL
	 *
	 * @return string The requested URL
	 */
	public function getUrl()
	{
		return $this->_requestUrl;
	}

	/**
	 * Return array of arguments
	 *
	 * @return array The array of passed arguments
	 */
	public function toArray()
	{
		return $this->_arguments;
	}

	/**
	 * Alias for toArray to fit Laravel convention
	 *
	 * @return array The array of passed arguments
	 */
	public function all()
	{
		return $this->toArray();
	}
	
	/**
	 * Validate using System validator
	 * 
	 * @param  array  $rules Rules to use to validate this
	 * @return void
	 */
	public function validate(array $rules) {
		$validator = new Validator();

		$validator->validate($this->toArray(), $rules);
	}
}

?>
