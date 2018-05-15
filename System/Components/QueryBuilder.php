<?php

namespace System\Components;

use System\Components\Relationships\Relationship;
use System\Components\Relationships\Belongs;
use System\Components\Relationships\Has;

class QueryBuilder extends AppComponent
{

    /**
     * The primary target table for query
     *
     * @var string
     */
    private $_table;

    /**
     * The primary key for the target table
     *
     * @var string
     */
    private $_primaryKey;

    /**
     * The ordering declaration
     *
     * @var string
     */
    private $_orderBy;

    /**
     * The columns to use in query
     *
     * @var array
     */
    private $_columns;

    /**
     * The values to assign to columns
     *
     * @var array
     */
    private $_values;

    /**
     * Array of relationships to establish joins
     *
     * @var array
     */
    private $_joins;

    /**
     * Array of where declarations
     *
     * @var array
     */
    private $_where;

    /**
     * Has the query been completely built?
     *
     * @var boolean  True for ready, False for not
     */
    private $_ready;

    /**
     * The QueryBuilder constructor
     *
     * @param string $table      The table to construct the query against
     * @param string $primaryKey The primary key to construct the query against
     */
    public function __construct($table, $primaryKey)
    {
        parent::__construct();

        $this->_table = $table;

        $this->_primaryKey = $primaryKey;

        $this->_where = array();

        $this->_orderBy = "";

        $this->_columns = array();

        $this->_joins = array();

        $this->_ready = false;
    }

    /**
     * Create the given object
     *
     * @param  array $objArray Array to use to create the object
     * @return void
     */
    public function create($objArray)
    {
        return $this->insert($objArray);
    }

    /**
     * Build get query with bindings
     *
     * @return string Select query with bindings
     */
    public function get()
    {
        return $this->_buildSelectComponents();
    }

    /**
     * Build get query with bindings using ID
     *
     * @return string Select query with bindings
     */
    public function find($id)
    {
        $this->_where[$this->_obj->getKey()] = $id;

        return $this->_buildSelectComponents();
    }

    /**
     * Add a relationship and return this query builder
     *
     * @return \System\Components\QueryBuilder
     */
    public function join(Relationship $relationship)
    {
        $this->_joins[] = $relationship;

        return $this;
    }

    /**
     * Add a where clause
     *
     * @return \System\Components\QueryBuilder
     */

    /**
     * Add an "and where" clause
     * @param  string      $column             The column to use in where clause
     * @param  string      $op                 The operation (if not "="), if empty, $value instead
     * @param  string|null $value              The value for the where clause if $op omitted
     * @return \System\Components\QueryBuilder The query builder for use in additional operations
     */
    public function where($column, $op, $value = null)
    {
        if(empty($value)) {
            $this->_where[] = array(' AND ', $column, $op);
            return $this;
        }
        $this->_where[] = array(' AND ', $column, $op, $value);
        return $this;
    }

    /**
     * Add an "or where" clause
     * @param  string      $column             The column to use in where clause
     * @param  string      $op                 The operation (if not "="), if empty, $value instead
     * @param  string|null $value              The value for the where clause if $op omitted
     * @return \System\Components\QueryBuilder The query builder for use in additional operations
     */
    public function orWhere($column, $op, $value = null)
    {
        if(empty($value)) {
            $this->_where[] = array('OR', $column, $op);
            return;
        }
        $this->_whereNot[] = array('OR', $column, $op, $value);

        return $this;
    }

    /**
     * Build insert components
     *
     * @param string The Key/Value pairs to be inserted
     * @return array Insert components
     */
    public function insert($columns)
    {
        $this->_columns = $columns;

        return $this->_buildInsertComponents();
    }

    /**
     * Build select components
     *
     * @param string The columns to select
     * @return DbQuery Select components
     */
    public function select($columns = null)
    {
        $this->_columns = $columns;

        return $this->_buildSelectComponents();
    }

    /**
     * Build update components
     *
     * @param string The Key/Value pairs to be updated
     * @return DbQuery Update components
     */
    public function update($columns)
    {
        $this->_columns = $columns;

        return $this->_buildUpdateComponents();
    }

    /**
     * Build delete components
     *
     * @return DbQuery Delete components
     */
    public function delete()
    {
        return $this->_buildDeleteComponents();
    }

    /**
     * Automagically build fetch query and associated value map
     *
     * @return DbQuery Query and value map
     */
    private function _buildSelectComponents()
    {
        $qry = "SELECT COLS FROM `".$this->_table."`";
        $qryMap = "";
        $keys = array();
        $values = array();
        if(empty($this->_columns)) {
            $dbQry = new DbQuery("SELECT * FROM `$this->_table` LIMIT 1", array());
            $result = $this->app->database->runQuery($dbQry);
            if(empty($result)) {
                $qry = str_replace('COLS', '*', $qry);
                return new DbQuery($qry, array_merge(array($qryMap), $values));
            }
            $columns = array_keys($result[0]);
            $selectors = array();
            foreach($columns as $column) {
                $subQry = "`$this->_table`.`$column` AS ";
                $subQry .= "`".$this->_table."_$column`";
                $selectors[] = $subQry;
            }

            foreach($this->_joins as $relationship) {
                $class = $relationship->getClass();
                $obj = new $class();
                $dbQry = new DBQuery("SELECT * FROM `".$obj->getTable()."` LIMIT 1", array());
                $result = $this->app->database->runQuery($dbQry);
                $columns = array_keys($result[0]);
                foreach($columns as $column) {
                    $subQry = "`".$obj->getTable()."`.`".$column."` AS ";
                    $subQry .= "`".$obj->getTable()."_".$column."`";
                    $selectors[] = $subQry;
                }
            }
            $qry = preg_replace('/COLS/', implode(", ", $selectors), $qry);
        }
        else {
            $columns = array();
            foreach($this->_columns as $key => $val) {
                $columns[] = "`$this->_table`.`$key` AS `".$this->_table."_$key`";
            }
            $qry = preg_replace('/COLS/', implode(',', $columns), $qry);
        }

        if(!empty($this->_joins)) {
            foreach($this->_joins as $relationship) {
                $class = $relationship->getClass();
                $src = $relationship->getSourceModel();
                $obj = new $class();
                if($relationship instanceof Belongs) {
                    $qry .= " RIGHT JOIN ";
                }
                else {
                    $qry .= " LEFT JOIN ";
                    $qry .= "`".$obj->getTable()."` ON ";
                    $qry .= "`".$src->getTable()."`.`".$src->getPrimaryKey()."` = ";
                    $qry .= "`".$obj->getTable()."`.`".$relationship->getKey()."`";
                }

            }
        }

        if(!empty($this->_where)) {
            $qry .= " WHERE ";
        }

        foreach($this->_where as $key => $value) {
            $input = $value[2];
            if(sizeof($value) == 4) {
                $input = $value[3];
            }
            if($key > 0) {
                $qry .= $value[0]." ";
            }
            $qry .= "`".$value[1]."` = ?";
            $qryMap .= $this->_getQryMapValueType($input);
            $values[] = $input;
        }

        if(!empty($this->_orderBy)) {
            $qry .= " ORDER BY `$this->_orderBy`";
        }

        return new DbQuery($qry, array_merge(array($qryMap), $values));
    }

    /**
	 * Automagically build the insert query and associated value map
	 *
	 * @return DbQuery  Query and value map
	 */
	private function _buildInsertComponents() {
		$qry = "INSERT INTO `".$this->_table."` (`KEYS`) VALUES (VALS)";
		$qryMap = "";
		$keys = array();
		$values = array();
		$questionMarks = array();
		foreach($this->_columns as $key => $value) {
			$qryMap .= $this->_getQryMapValueType($value);
			array_push($keys, $key);
			array_push($values, $value);
			array_push($questionMarks, '?');
		}
		$qry = preg_replace('/KEYS/', implode('`,`', $keys), $qry);
		$qry = preg_replace('/VALS/', implode(",", $questionMarks), $qry);

		return new DbQuery($qry, array_merge(array($qryMap), $values));
	}

	/**
	 * Automagically build the update query and associated value map
	 *
	 * @return DbQuery  Query and value map
	 */
	private function _buildUpdateComponents() {
		$qry = "UPDATE `".$this->_table."` SET ";
        $qryMap = "";
        $values = array();
		list($qryUpdate, $qryMapUpdate) = $this->_inputBuilder("updates");
        $qry .= $qryUpdate;
        $qryMap .= array_shift($qryMapUpdate);
        $values = array_merge($values, $qryMapUpdate);

        $qry .= " WHERE ";

        list($qryUpdate, $qryMapUpdate) = $this->_inputBuilder("where");
        $qry .= $qryUpdate;
        $qryMap .= array_shift($qryMapUpdate);
        $values = array_merge($values, $qryMapUpdate);

		return new DbQuery($qry, array_merge(array($qryMap), $values));
	}

    /**
     * Automagically build the delete query and associated value map
     *
     * @return DbQuery  Query and value map
     */
    private function _buildDeleteComponents()
    {
        $qry = "DELETE FROM `".$this->_table."` WHERE ";
        $qryMap = "";

        list($qryUpdate, $qryMapUpdate) = $this->_inputBuilder("where");
        $qry .= $qryUpdate;
        $qryMap .= array_shift($qryMapUpdate);

        return new DbQuery($qry, array_merge(array($qryMap), array_splice($qryMapUpdate, 0, 1)));
    }

    /**
     * A generic `key` = 'val' pair builder
     *
     * @param  string $section The variable to pull values from
     * @return array           Array (query extension, query map extension)
     */
    private function _inputBuilder($section) {
        $qryMap = "";
        $subQry = "";
        $values = array();
        $array = ($section === "updates") ? $this->_columns : $this->{"_".$section};

        $addGlue = false;
        foreach($array as $key => $value) {
            $meta = $this->_getMetaFromMap($section, $key, $value);
            if($key !== $this->_primaryKey) {
                $glue = $addGlue ? $meta[0] : "";
                $qryMap .= $meta[1];
                $values[] = $meta[2];
                $subQry .= $glue.$meta[3];
                $addGlue = true;
            }
        }
        return array($subQry, array_merge(array($qryMap), $values));
    }

	/**
	 * Map value to query map character
	 *
	 * @param  mixed  $value The value to be mapped
	 * @return char          The character representing the DB type
	 */
	private function _getQryMapValueType($value) {
		if(is_numeric($value)) {
			return "i";
		}
		return "s";
	}

    /**
     * Pull value from various context maps
     *
     * @param  string $section Variable context (where, updates, etc.)
     * @param  string $key     Accessor for the value
     * @param  array  $value   Information used to build subquery (glue, qryMapValueType, value, qryString)
     * @return string          The value pulled from the map
     */
    private function _getMetaFromMap($section, $key, $value)
    {
        switch($section) {
            case 'updates':
                return array(',', $this->_getQryMapValueType($value), $value, "`".$key."` = ?");
            case 'where':
                $map = $value;
                $value = $map[sizeof($map) - 1];
                $modifier = $map[0];
                $column = $map[1];
                $op = sizeof($map) === 4 ? $map[2] : "=";
                return array($modifier, $this->_getQryMapValueType($value), $value, "`$column` $op ?");
        }
    }
}
