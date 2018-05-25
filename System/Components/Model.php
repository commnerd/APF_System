<?php

namespace System\Components;

use System\Components\Relationships\BelongsToMany;
use System\Components\Relationships\BelongsTo;
use System\Components\Relationships\HasMany;
use System\Components\Relationships\HasOne;
use System\Components\DbConnection;
use System\Services\TextTransforms;
use System\Interfaces\Relationship;
use IteratorAggregate;
use ReflectionClass;
use ErrorException;

/**
 * Model for use by the system
 */
abstract class Model extends AppComponent implements IteratorAggregate
{
	/**
	 * Find or fail exception
	 *
	 * @var string
	 */
	const ERROR_EXCEPTION_NOT_FOUND = "The model could not be found.";

	/**
	 * Deletion error exception message
	 *
	 * @var string
	 */
	const ERROR_EXCEPTION_DELETE    = "No context for deletion.";

	/**
	 * Deletion error exception message
	 *
	 * @var string
	 */
	const ERROR_EXCEPTION_UPDATE    = "No context for update.";

	/**
	 * Error for when element not found
	 *
	 * @var string
	 */
	const ERROR_EXCEPTION_GET       = "Variable not found.";

	/**
	 * Error passed for unintended arguments being passed
	 *
	 * @var string
	 */
	const ERROR_PRIVATE_ARGUMENTS   = "Passed arguments not intended for framework user.";

	/**
	 * Method only intended for system
	 *
	 * @var string
	 */
	const ERROR_SYSTEM_METHOD       = "This method is intended for the framework only.";

	/**
	 * Error passed for unintended arguments being passed
	 *
	 * @var string
	 */
	const ERROR_IMPROPER_METHOD_USE = "Improper method use.";

	/**
	 * A central location to grab the current database
	 * 
	 * @var DbConnection
	 */
	public static $database;

	/**
	 * Build queries to pass to the database handler
	 *
	 * @var QueryBuilder
	 */
	private $_queryBuilder;

	/**
	 * The database connection to work with
	 *
	 * @var DbConnection
	 */
	private $_db;

	/**
	 * "With" registry
	 * @var [type]
	 */
	private $_with;

	/**
	 * Values as read from database
	 */
	private $_originalValues;

	/**
	 * Table maintaining this class's data
	 *
	 * @var string
	 */
	protected $table;

	/**
	 * Primary key for the class
	 *
	 * @var string
	 */
	protected $primaryKey = "id";

	/**
	 * Attributes to be handled by this class
	 *
	 * @var array
	 */
	protected $attributes;

	/**
	 * Casting declarations to maintain for this class
	 *
	 * @var array
	 */
	protected $casts;

	/**
	 * The array of variables intended for automagic filling
	 *
	 * @var array
	 */
	protected $fillable;

	/**
	 * Static method that leverages QueryBuilder
	 *
	 * @param  string $method      The name of the QueryBuilder method to call
	 * @param  array  $args        Arguments to pass to the method
	 * @return array|QueryBuilder  Result set or QueryBuilder
	 */
	public static function __callStatic($method, $args)
	{
		$class = get_called_class();

		$obj = new $class();
		if($obj instanceof Model) {
			return call_user_func_array(array($obj, $method), $args);
		}

		return $obj;
	}

	/**
	 * Forward function call if not explicitely defined
	 *
	 * @param  string $method The method to forward
	 * @param  array  $args   The args to pass
	 * @return mixed          The returned value
	 */
	public function __call($method, $args) {

		$methods = get_class_methods($this);
		if(in_array("___".$method, $methods)) {
			return call_user_func_array(array($this, "___".$method), $args);
		}

		$methods = get_class_methods($this->_queryBuilder);
		if(in_array($method, $methods)) {
			$query = call_user_func_array(array($this->_queryBuilder, $method), $args);
			if($query instanceof DbQuery) {
				$result = $this->_db->runQuery($query);
				if(is_array($result)) {
					$this->fill($result);
				}
				if(is_integer($result)) {
					return $this->findOrFail($result);
				}
			}
			return $this;
		}

	}

	/**
	 * Constructor for the class
	 */
	public function __construct()
	{
		parent::__construct();

		if(isset($this->app)) {
			$this->_db = $this->app->database;
		}

		if(isset(self::$database)) {
			$this->_db = self::$database;
		}
		
		$this->_queryBuilder = new QueryBuilder($this->getTable(), $this->getPrimaryKey(), $this->_db);

		$this->attributes = array();
		$this->_with = array();

		if(empty($this->table) || !is_string($this->table)) {
			$className = $this->_getClassName();
			$this->table = $this->getTable();
		}

		$this->_instantiateArrayIfNecessary($this->casts);
		$this->_instantiateArrayIfNecessary($this->fillable);
		$this->_instantiateArrayIfNecessary($this->_originalValues);
	}

	/**
	 * Register database connection
	 * 
	 * @param  DbConnection $db The connection to use to run queries
	 * @return void
	 */
	public function registerDatabase(DbConnection $db)
	{
		$this->_db = $db;
		$this->_queryBuilder = new QueryBuilder($this->getTable(), $this->getPrimaryKey(), $db);
	}

	/**
	 * Automagic variable retrieval
	 *
	 * @param  string $name Name of variable to retrieve
	 * @return mixed        Value of retrieved variable
	 */
	public function __get($name)
	{
		$methods = get_class_methods(get_class($this));
		if(isset($this->attributes[$name])) {
			return $this->attributes[$name];
		}
		if(in_array($name, $methods)) {
			$relationship = $this->{$name}();
			if($relationship instanceof Relationship) {
				$results = $relationship->fetch();
				$this->attributes[$name] = $results;
			}
			return $this->attributes[$name];
		}

		if($name === "attributes" && !isset($this->attributes['attributes'])) {
			return $this->attributes;
		}

		throw new ErrorException(self::ERROR_EXCEPTION_GET);
	}

	/**
	 * Automagic variable setting
	 *
	 * @param string $name  Name of variable to retrieve
	 * @param mixed  $value Value of variable
	 */
	public function __set($name, $value)
	{
		$this->attributes[$name] = $value;
	}

	/**
	 * Grab the related model
	 *
	 * @param  string  $class      The related class
	 * @param  string  $foreignKey The foreign key to use in lookup
	 * @param  string  $table      The table to look in if needing override
	 * @return Relationship		   The relationship model
	 */
	public function hasOne($class, $foreignKey = null, $table = null)
	{
		return new HasOne($this, $class, $foreignKey, $table);
	}

	/**
	 * Grab the related models
	 *
	 * @param  string  $class      The related class
	 * @param  string  $foreignKey The foreign key to use in lookup
	 * @param  string  $table      The table to look in if needing override
	 * @return Relationship		   The relationship model
	 */
	public function hasMany($class, $foreignKey = null, $table = null)
	{
		return new HasMany($this, $class, $foreignKey, $table);
	}

	/**
	 * Grab the related model
	 *
	 * @param  string  $class      The related class
	 * @param  string  $foreignKey The foreign key to use in lookup
	 * @param  string  $table      The table to look in if needing override
	 * @return Relationship		   The relationship model
	 */
	public function belongsTo($class, $foreignKey = null, $table = null)
	{
		return new BelongsTo($this, $class, $foreignKey, $table);
	}

	/**
	 * Grab the related models
	 *
	 * @param  string  $class      The related class
	 * @param  string  $foreignKey The foreign key to use in lookup
	 * @param  string  $table      The table to look in if needing override
	 * @return Relationship		   The relationship model
	 */
	public function belongsToMany($class, $foreignKey = null, $table = null)
	{
		return new BelongsToMany($this, $class, $foreignKey, $table);
	}

	/**
	 * Get the table for the model
	 *
	 * @return string The table name for the model
	 */
	public function getTable()
	{
		if(isset($this->table)) {
			return $this->table;
		}
		$reflect = new ReflectionClass($this);
		$name = $reflect->getShortName();
		$name = TextTransforms::camelCaseToSnakeCase($name);
		return TextTransforms::singleToPlural($name);
	}

	/**
	 * Get the primary key for the model
	 *
	 * @return string The column representing the primary key
	 */
	public function getPrimaryKey()
	{
		return $this->primaryKey;
	}

	/**
	 * Get the primary key value for the model
	 *
	 * @return integer The ID for the given model
	 */
	public function getKey()
	{
		if(!isset($this->attributes[$this->primaryKey])) {
			return null;
		}
		return $this->attributes[$this->primaryKey];
	}

	/**
	 * Fill model from array
	 *
	 * @param  array  $attributes Array of items to populate model with
	 * @param  array  $results 	  Results from a Database Query (not for use by framework user)
	 * @return Model              Whatever was just filled
	 */
	public function fill($attributes, $results = null) {
		if(isset($results) && !$this->_calledFromSystem()) {
			throw new ErrorException(self::ERROR_PRIVATE_ARGUMENTS);
		}
		if($this->_calledFromSystem()) {
			foreach(array_keys($attributes) as $attribute) {
				$table = $this->table;

				if(preg_match("/^".$table."_(.*)$/", $attribute, $matches)) {
					$this->attributes[$matches[1]] = $attributes[$attribute];
					$this->_originalValues[$matches[1]] = $attributes[$attribute];
				}

			}
			if(!empty($this->_with) && !empty($results)) {

				foreach($this->_with as $key => $relation) {
					$this->attributes[$key] = $this->fillChildren($results, $relation);
					array_pop($this->_with);

					if(empty($this->attributes[$key])) {
						unset($this->attributes[$key]);
					}
				}
			}
		}
		else {
			foreach($this->fillable as $key) {
				$this->attributes[$key] = $attributes[$key];
			}
		}
		return $this;
	}

	/**
	 * Fill children in presence of _with statements
	 *
	 * @param  array             $results  Model list
	 * @param  Relationship|null $relation The relationship
	 * @return array                       Filled child models
	 */
	public function fillChildren(array $results, Relationship $relation = null)
	{
		if(isset($results) && !$this->_calledFromSystem()) {
			throw new ErrorException(self::ERROR_SYSTEM_METHOD);
		}
		if(!isset($relation)) {
			foreach($this->_with as $relation) {
				$this->fillChildren($results, $relation);
			}
			return;
		}
		$objs = array();
		$class = $relation->getClass();

		foreach($results as $row) {
			if($row[$this->table."_".$this->primaryKey] === $this->getKey()) {
				$obj = new $class();
				$with = $relation->getWith();
				if(!empty($with)) {
					$obj->with($with);
				}
				if(!empty($row[$obj->getTable()."_".$obj->getPrimaryKey()])) {
					$objs[$row[$obj->getTable()."_".$obj->getPrimaryKey()]] = $obj->fill($row, $results);
				}
			}
		}
		return $objs;
	}

	/**
	 * Add or update the model in the database
	 *
	 * @return integer          The primary key value of the saved item
	 */
	public function save()
	{
		if(isset($this->attributes[$this->primaryKey])) {
			$this->_update();
		}
		else {
			$this->attributes[$this->primaryKey] = $this->_insert();
		}
		foreach($this->attributes as $attribute => $value) {
			if($value instanceof Model) {
				$value->save($cascade);
			}
		}
		return $this->attributes[$this->primaryKey];
	}

	/**
	 * Implemented for IteratorAggregate interface
	 *
	 * @return iterable Attributes array
	 */
	public function getIterator() {
        return $this->attributes;
    }

    /**
	 * Method to allow you to set the relationships
	 * 
	 * @param array $with The with array to be set locally
	 * @return void
	 */
	public function setWithRelationships(array $with) {
		$this->_with = $with;
	}

	/**
	 * Pull related models with current model selection (intended for static or instance calls)
	 *
	 * @param  string            $children "." delimited list of child element variable names
	 * @param  QueryBuilder|null $qb
	 * @return Model                       The parent-most model
	 */
	private function ___with($children, QueryBuilder $qb = null)
	{
		if(empty($qb)) {
			$qb = $this->_queryBuilder;
		}
		if(is_array($children)) {
			foreach($children as $index => $child) {
				if(is_string($child)) {
					$this->with($child, $qb);
				}
			}
			return $this;
		}
		$children = explode('.', $children);
		$child = array_pop($children);
		$children = implode('.', $children);
		$relation = $this->{$child}();
		$qb->join($relation);
		if(!empty($children)) {
			$relation->setWith($children);
		}
		$this->_with[$child] = $relation;
		return $this;
	}

	/**
	 * Pull entire list of model (intended for static or instance calls)
	 *
	 * @return array Array of models of the type that was called
	 */
	private function ___all()
	{
		$class = get_called_class();
        $query = call_user_func_array(array($this->_queryBuilder, 'get'), array());
        $results = $this->_db->runQuery($query);
        $objs = array();
        if(!empty($results)) {
                foreach($results as $row) {
                        $obj = new $class();
                        $obj->setWithRelationships($this->_with);
                        $obj->fill($row, $results);
                        $objs[$obj->getKey()] = $obj;
                }
        }
        return $objs;

	}

	/**
	 * Find a record by primary key
	 *
	 * @param  integer $id The primary key for the model
	 * @return Model|null  The model if it exists or null
	 */
	private function ___find($id)
	{
		$query = $this->_queryBuilder->where($this->getPrimaryKey(), $id)->get();

		$result = $this->_db->runQuery($query);

		if(!empty($result)) {
			$this->fill($result[0]);
		}

		return $this;
	}

	/**
	 * Find a record by primary key, fail if not found
	 *
	 * @param  integer $id The primary key for the model
	 * @return Model|null  The model if it exists or null
	 */
	private function ___findOrFail($id)
	{
		$obj = $this->find($id);
		if(empty($obj)) {
			throw new ErrorException(self::ERROR_EXCEPTION_NOT_FOUND);
		}
		return $obj;
	}

	/**
	 * Delete a given record
	 *
	 * @param boolean $cascade  If true, delete children and all subchildren
	 * @return void
	 */
	private function ___delete($id)
	{
		if(empty($this->primaryKey)) {
			throw new ErrorException(self::ERROR_EXCEPTION_DELETE);
		}

		$column = $this->getPrimaryKey();
		$query = $this->_queryBuilder->where($column, $id)->delete();
		$this->_db->runQuery($query);
	}

	/**
	 * Return all values as array
	 *
	 * @return array All attributes
	 */
	public function toArray()
	{
		foreach($this->attributes as $key => $value) {
			if(is_array($value)) {
				$this->attributes[$key] = $this->_cascadeToArray($value);
			}
			if($value instanceof Model) {
				$this->attributes[$key] = $value->toArray();
			}
		}
		return $this->attributes;
	}

	/**
	 * Cascade down array chains looking for models to cast to arrays
	 *
	 * @param  array  $array Arrays full of potential models
	 * @return array Arrays of converted Models
	 */
	private function _cascadeToArray(array $array) {
		foreach($array as $index => $value) {
			if(is_array($value)) {
				$array[$index] = $this->_cascadeToArray($value);
			}
			if($value instanceof Model) {
				$array[$index] = $value->toArray();
			}
		}
		return $array;
	}

	/**
	 * Read models from database and return as array of records
	 *
	 * @param  DbQuery $query The DbQuery object to query the DB with
	 * @return array          Array of models from a database call
	 */
	public function readFromDatabase(DbQuery $query)
	{
		return $this->_db->runQuery($query);
	}

	/**
	 * Get model from the database
	 *
	 * @return Model
	 */
	private function ___get()
	{
		$query = $this->_queryBuilder->get();
		$records = $this->_db->runQuery($query);
		if(sizeof($records) > 1) {
			 foreach($records as $index => $record) {
                    $class = get_class($this);
                    $obj = new $class();
                    $obj->fill($record);
                    $records[$index] = $obj;
            }
            return $records;
		}
		if(sizeof($records) === 1) {
			return $this->fill($records[0]);
		}
		return array();
	}

	/**
	 * Update model in the database
	 *
	 * @return void
	 */
	private function _update()
	{
		$query = $this->_queryBuilder->update($this->toArray());
		$this->_db->runQuery($query);
	}

	/**
	 * Insert model into the database
	 *
	 * @return integer Primary key
	 */
	private function _insert() {
		$query = $this->_queryBuilder->insert($this->toArray());
		return $this->_db->runQuery($query);
	}

	/**
	 * Get the class name sans namespace
	 *
	 * @return string Class name
	 */
	private function _getClassName()
	{
		$class = new \ReflectionClass($this);
		return $class->getShortName();
	}

	/**
	 * Utility function to make an array if it's supposed to be an array and it's not
	 *
	 * @param  mixed  &$var The variable to transform
	 * @return void
	 */
	private function _instantiateArrayIfNecessary(&$var) {
		if(empty($var) || !is_array($var)) {
			$var = array();
		}
	}

	/**
	 * Determine whether calls are made outside of "System" namespace
	 *
	 * @return boolean  True = Call made from "System" namespace, False = Call made outside "System" namespace
	 */
	private function _calledFromSystem()
	{
		$trace = debug_backtrace();
		return preg_match('/^System/', $trace[2]['class']) || $trace[2]['object'] instanceof Model;
	}
}
