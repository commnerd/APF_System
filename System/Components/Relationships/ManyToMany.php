<?php

namespace System\Components\Relationships;

use System\Components\DbConnection;
use System\Components\QueryBuilder;
use System\Components\Model;

class ManyToMany extends Relationship
{
    /**
     * An array of models associated to the sourceModel
     *
     * @var array<Model> $_relationships
     */
    private $_relationships;

	/**
	 * The database in use by the app
	 *
	 * @var DbConnection $_db
	 */
	private $_db;

	/**
	 * The column used to associate the remote object
	 *
	 * @var string $_joinColumn
	 */
	private $_joinColumn;

	/**
     * Build the relationship object
     *
     * @param DbConnection $db          The database in use
     * @param Model        $sourceModel The model requesting the relationship
     * @param string       $class       The target class
     * @param string       $column      The overriding column
     * @param string       $joinColumn  The column used to associate the remote object
     * @param string       $table       The overriding table
     */
    public function __construct(DbConnection $db, Model $sourceModel, $class, $column, $joinColumn, $table) {
    	parent::__construct($sourceModel, $class, $column, $table);

    	$this->_db = $db;

    	$this->_joinColumn = $joinColumn;

        $this->_relationships = array();
    }

    /**
     * Add a value to the join table
     *
     * @param Model $item Related model
     */ 
    public function add(Model $item)
    {
        $this->_relationships[] = $item;
    }

    /**
     * Sync relationships
     *
     * @param array<int|Model> $targets  List of IDs or Objects to sync
     */
    public function sync(array $targets)
    {
        $this->_stripJoins();
        $this->_relationships = array();
        $class = $this->class;
        foreach($targets as $target) {
            if(is_numeric($target)) {
                $this->_relationships[] = $class::findOrFail($target);
            }
            if($target instanceof Model) {
                $this->_relationships[] = $target;
            }
        }
        $this->saveRelationships();
    }

    /**
     * Save relationships
     */
    public function saveRelationships()
    {
        $keys = array();
        foreach($this->_relationships as $child) {
            $id = $child->getKey();
            if(empty($id)) {
                $child->save();
                $id = $child->getKey();
            }
            $keys[] = $id;
        }
        $this->_placeJoins($keys);

    }

    /**
     * Remove relationships
     *
     * @return array<Model> $_relationships Array of related models
     */
    public function deleteRelationships()
    {
        $this->_stripJoins();
    }

	/**
     * Fetch the related object
     *
     * @return Model       The related model
     */
    public function fetch()
    {
        $key = $this->getKey();
        $class = $this->class;
        $obj = new $class();
        $obj->{$this->column} = $this->sourceModel->getKey();
        $qb = new QueryBuilder($this->table, $this->column, $this->_db);

        $qry = $qb->where($this->column, $this->sourceModel->getKey())->get();
        $results = $this->_db->runQuery($qry);

        $ids = array();
        if($results) {
            foreach($results as $result) {
                $ids[] = $result[$this->table."_".$this->_joinColumn];
            }

            $result = $class::where($obj->getPrimaryKey(), 'IN', $ids)->get();
            if(empty($result)) {
                return $obj;
            }
            foreach($result as $key => $obj) {
               $obj->{$this->column} = $this->sourceModel->getKey();
               $results[$key] = $obj;
            }
        }

        return $results;
    }

    /**
     * Remove current relationships
     */
    public function _stripJoins()
    {
        $qb = new QueryBuilder($this->table, $this->column, $this->_db);

        $qry = $qb->where($this->column, $this->sourceModel->getKey())->delete();

        $this->_db->runQuery($qry);
    }

    public function _placeJoins($joins) {
        $qb = new QueryBuilder($this->table, $this->column, $this->_db);
        foreach($joins as $relationship) {
            $pKey = $this->sourceModel->getKey();
            $qry = $qb->insert(array(
                ($this->column) => $pKey,
                ($this->_joinColumn) => $relationship
            ));
            $this->_db->runQuery($qry);
        }
    }
}