<?php 

namespace System\Interfaces;

/**
 * Interface for the request object
 */
interface Request
{
	/**
	 * Get a parameter passed in from the request
	 * 
	 * @param  string $name  The label for the corresponding argument
	 * @return string|array  The corresponding data
	 */
	public function __get($name);

	/**
	 * Retrieve header information
	 * 
	 * @param  string $name The name of the header to retrieve
	 * @return string       The header value
	 */
	public function getHeader($name);

	/**
	 * Get the request method
	 * 
	 * @return string The request method
	 */
	public function getMethod();
}

?>