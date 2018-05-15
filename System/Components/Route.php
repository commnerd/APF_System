<?php

namespace System\Components;

use ErrorException;

class Route
{
	/**
	 * The seperator for the controller/method
	 */
	const CONTROLLER_METHOD_SEPARATOR = "#";

    /**
     * The route name
     *
     * @var string
     */
	private $_name;

    /**
     * The route target (fully qualified controller name and method delimited by "#")
     *
     * @var string
     */
	private $_target;

    /**
     * The params to pass to the method
     *
     * @var array
     */
	private $_params;

	/**
	 * The constructor for the route
	 *
	 * @param array $routeDef Array defining the route
	 */
    public function __construct($routeDef)
    {
        if(!is_array($routeDef)) {
            throw new ErrorException("Route definition must be an array.");
        }

        if(isset($routeDef['target'])) {
        	$this->_target = $routeDef["target"];
        }
        else {
        	throw new ErrorException("Route target not set.");
        }

        if(isset($routeDef['params'])) {
        	$this->_params = $routeDef['params'];
        }
        else {
        	throw new ErrorException("Route params not set.");
        }

        if(isset($routeDef['name'])) {
        	$this->_name = $routeDef['name'];
        }
    }

    /**
     * Getter for private vars
     *
     * @param  string $label The variable you want to get
     * @return string|array  The value for that variable
     */
    public function __get($label)
    {
    	if($label === 'name' || $label === 'params') {
    		return $this->{"_$label"};
    	}

    	$targetAry = explode(self::CONTROLLER_METHOD_SEPARATOR, $this->_target);
    	if($label === 'controller') {
    		return $targetAry[0];
    	}
    	if($label === 'method') {
    		if(sizeof($targetAry) === 2) {
    			return $targetAry[1];
    		}
    		return "index";
    	}
    }
}
