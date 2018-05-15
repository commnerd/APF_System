<?php

namespace System\Components;

/**
 * Model for globally fetching private variables
 */
abstract class PackageComponent extends AppComponent
{
    	/**
    	 * Get a parameter passed in from the request
    	 *
    	 * @param  string $name The label for the corresponding argument
    	 * @return mixed        The corresponding data
    	 */
    	public function __get($name) {
    		if(isset($this->{"_".$name})) {
    			return $this->{"_".$name};
    		}
    		return null;
    	}
}
