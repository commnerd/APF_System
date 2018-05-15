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

	public function getClass()
	{
		return $this->class;
	}

	public function getSourceModel()
	{
		return $this->sourceModel;
	}

	public function getKey()
	{
		if(isset($this->column)) {
            return $this->column;
        }

		return $this->guessKey();
	}

    public function getQuery()
    {
		if(!$this->query instanceof DbQuery) {
			throw new ErrorException(self::EXCEPTION_NOT_QUERY);
		}
        return $this->query;
    }

    public function buildResultSet($set) {
        $result = array();
        $class = $this->class;
        foreach($set as $item) {
            $obj = new $class();
            $result[] = $obj->fill($item);
        }
        return sizeof($result) === 1 ? array_pop($result) : $result;
    }

	public function setWith($str = null) {
		$this->_with = $str;
	}

	public function getWith() {
		return $this->_with;
	}
}
