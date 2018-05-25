<?php

namespace System\Components\Relationships;

use System\Interfaces\Relationship as RelationshipInterface;
use System\Components\DbQuery;
use System\Components\Model;
use ErrorException;

abstract class Relationship implements RelationshipInterface
{
	/**
	 * Message to be passed when not query
	 *
	 * @var string
	 */
	const EXCEPTION_NOT_QUERY = "This is not type DbQuery.";

    /**
     * The model requesting the resources
     *
     * @var \System\Components\Model
     */
	protected $sourceModel;

    /**
     * The resulting query
     *
     * @var \System\Components\DbQuery
     */
    protected $query;

    /**
     * The related class
     *
     * @var string
     */
    protected $class;

    /**
     * The overridden table
     *
     * @var string
     */
    protected $table;

	/**
     * The overridden column
     *
     * @var string
     */
    protected $column;

	/**
	 * Carryover with ars
	 */
	private $_with;


    /**
     * Build the relationship object
     *
     * @param Model  $sourceModel The model requesting the relationship
     * @param string $class       The target class
     * @param string $column      The overriding column
     * @param string $table       The overriding table
     */
    public function __construct(Model $sourceModel, $class, $column = null, $table = null) {
    	$this->sourceModel = $sourceModel;

        $this->class = $class;

        $this->column = $column;

        $this->table = $table;
    }

    /**
     * Get the source class
     * 
     * @return string Class name
     */
	public function getClass()
	{
		return $this->class;
	}

    /**
     * Get the source model object
     * 
     * @return Model The model reference
     */
	public function getSourceModel()
	{
		return $this->sourceModel;
	}

    /**
     * Get the primary key column name, guess if null
     * 
     * @return string The column name for the primary key
     */
	public function getKey()
	{
		if(isset($this->column)) {
            return $this->column;
        }

		return $this->guessKey();
	}

    /**
     * Get the result from the relationship
     * @param  array $set  The raw database rows
     * @return mixed       Model or array of models
     */
    public function buildResultSet($set) {
        $result = array();
        $class = $this->class;
        foreach($set as $item) {
            $obj = new $class();
            if(empty($item)) return;
            if(is_object($item)) $item = $item->toArray();
            $result[] = $obj->fill($item);
        }
        return $result;
    }

    /**
     * Set the _with variable used to label relationships
     * 
     * @param array|string $str String or array of strings labeling relationships
     */
	public function setWith($str = null) {
		$this->_with = $str;
	}

    /**
     * Get the with variable
     * 
     * @return array|string $str String or array of strings labeling relationships
     */
	public function getWith() {
		return $this->_with;
	}
}
