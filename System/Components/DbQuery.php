<?php

namespace System\Components;

use System\Services\TextTransforms;
use System\Components\DbConnection;

/**
 * Model for use by the DbConnection system
 */
class DbQuery extends PackageComponent
{
	/**
	 * The query string to be processed
	 *
	 * @var string
	 */
    protected $_query;

    /**
     * The bindings to use in the query
     *
     * @var array
     */
    protected $_bindings = null;

    /**
     * Construct the query
     * @param string $query    The query
     * @param array  $bindings The bindings
     */
    public function __construct($query, $bindings)
    {
        $this->_query = $query;
        if(!empty($bindings[0])) {
            $this->_bindings = $bindings;
        }
    }
}
