<?php

namespace System\Components;

use \System\Components\Response;
use ErrorException;

class Response extends PackageComponent
{
	/**
	 * Response type constants
	 */
	const TYPE_TEMPLATE       = "template";
	const TYPE_REDIRECT       = "redirect";

	/**
	 * Error constant(s)
	 */
	const ERROR_TYPE_REDIRECT = "The redirect must have an associated route.";

	/**
	 * The response code to return to the browser
	 *
	 * @var integer
	 */
	protected $_code = 200;

	/**
	 * The type of response
	 *
	 * @var string
	 */
	protected $_type = "template";

	/**
	 * The template to use for the response if $_type === "template"
	 *
	 * @var string
	 */
	protected $_template;

	/**
	 * Parameters to pass to the template
	 *
	 * @var array
	 */
	protected $_params;

	/**
	 * Route/Path for a redirect
	 * 
	 * @var string
	 */
	protected $_route;

	/**
	 * Build the Response
	 *
	 * @param array $params The parameters to pass to the template
	 */
	public function __construct(array $params = array())
	{
		if($this->_type === self::TYPE_REDIRECT && !isset($params['route'])) {
			throw new ErrorException(self::ERROR_TYPE_REDIRECT);
		}

		foreach($params as $key => $val) {
			if(property_exists($this, "_".$key)) {
				$this->{"_".$key} = $params[$key];
				unset($params[$key]);
			}
		}

		$this->_params = $params;
	}
}

?>
