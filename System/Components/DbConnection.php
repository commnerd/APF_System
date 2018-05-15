<?php

namespace System\Components;

use Mysqli;

class DbConnection extends AppComponent
{

	protected $connection = null;

	public function __construct($username, $password, $hostname, $dbname, $port) {
		parent::__construct();
        $this->connection = new mysqli($hostname, $username, $password, $dbname, $port);
		if ($this->connection->connect_errno) {
		    echo "Failed to connect to MySQL: (" . $this->connection->connect_errno . ") " . $this->connection->connect_error;
		}
	}

	public function __destruct() {
	   	//close the connection
		$this->connection->close();
	}

    public function getDbInfo($pSQL) {
		$stmt = $this->connection->prepare($pSQL);
	    $stmt->execute();
	    $row = $this->bind_result_array($stmt);
	    if(!$stmt->error)
	    {
	        while($stmt->fetch())
	            $dataArray = $row;

	    }
	    $stmt->close();
	    if (isset($dataArray)) {
	    	return $dataArray;
	    } else {
	    	return;
	    }

    }

	public function runQuery(DbQuery $query)
	{
		if(preg_match('/^(SELECT|INSERT|UPDATE|DELETE)/', $query->query, $matches)) {
			switch(strtoupper($matches[0])) {
				case 'SELECT':
					return $this->getCustomQueries($query);
				case 'INSERT':
					return $this->addRecord($query);
				case 'UPDATE':
					return $this->updateRecord($query);
				case 'DELETE':
					return $this->deleteRecord($query);
			}
		}
		return array();
	}

    private function getCustomQueries(DbQuery $query) {
		$pSQL = $query->query;
 		$pTheBindVal = $query->bindings;
		$stmt = $this->connection->prepare($pSQL);
		if (!empty($pTheBindVal)) {
			call_user_func_array(array($stmt, 'bind_param'), $this->_refValues($pTheBindVal));
			//$stmt->bind_param("i", $pTheBindVal);
		}
	    $stmt->execute();

	    // print_r($stmt->error);
	    $stmt->store_result();
	    $row = $this->bind_result_array($stmt);
	    if(!$stmt->error)
	    {

	        while($stmt->fetch()) {

			    foreach( $row as $key=>$value )
			    {
			        $row_tmb[ $key ] = $value;
			    }

	            $dataArray[] = $row_tmb;

			}
	    }
	    $stmt->close();
	    if (isset($dataArray)) {
	    	return $dataArray;
	    	unset($dataArray);
	    } else {
	    	return;
	    }

    }

    private function addRecord(DbQuery $query) {
		$pSQL = $query->query;
		$pTheBindVal = $query->bindings;
    	$tempBindValArr = implode("||", $pTheBindVal);
    	$tempBindValArr = explode("||", $tempBindValArr);
		//var_dump($pSQL);
		$stmt = $this->connection->prepare($pSQL);
		//var_dump($stmt);
		if (!is_null($pTheBindVal)) {
			//print_r($pTheBindVal);
			$ref    = new \ReflectionClass('mysqli_stmt');
			//print_r($ref);
			$method = $ref->getMethod("bind_param");
			//print_r($method);
			$method->invokeArgs($stmt,$this->_refValues($tempBindValArr));

			//call_user_func_array(array($stmt, 'bind_param'), $pTheBindVal);
			//$stmt->bind_param("i", $pTheBindVal);
		}
		unset($tempBindValArr);
	    $stmt->execute();
	    $newID = $stmt->insert_id;
	    $stmt->close();
	    //echo '<p>New ID: '.$newID.'<p>';
	    return $newID;
    }

    private function updateRecord(DbQuery $query) {
		$pSQL = $query->query;
		$pTheBindVal = $query->bindings;
    	$tempBindValArr = implode("||", $pTheBindVal);
    	$tempBindValArr = explode("||", $tempBindValArr);
		$stmt = $this->connection->prepare($pSQL);
		if (!is_null($pTheBindVal)) {
			//print_r($pTheBindVal);
			$ref    = new \ReflectionClass('mysqli_stmt');
			//print_r($ref);
			$method = $ref->getMethod("bind_param");
			//print_r($method);
			$method->invokeArgs($stmt,$this->_refValues($tempBindValArr));

			//call_user_func_array(array($stmt, 'bind_param'), $pTheBindVal);
			//$stmt->bind_param("i", $pTheBindVal);
		}
		unset($tempBindValArr);

	    ////var_dump($stmt);
	    $stmt->execute();
	    // $newID = $stmt->update_id;
	    $stmt->close();
	    //echo '<p>New ID: '.$newID.'<p>';
	    //return $newID;
    }

    private function deleteRecord(DbQuery $query) {
		$pSQL = $query->query;
		$pTheBindVal = $query->bindings;
		$stmt = $this->connection->prepare($pSQL);
		if (!is_null($pTheBindVal)) {
			call_user_func_array(array($stmt, 'bind_param'), $this->_refValues($pTheBindVal));
			//$stmt->bind_param("i", $pTheBindVal);
		}
	    $stmt->execute();
    }


	/*
	 * Utility function to automatically bind columns from selects in prepared statements to
	 * an array
	 */
	private function bind_result_array($stmt)
	{
	    $meta = $stmt->result_metadata();
	    $result = array();
	    while ($field = $meta->fetch_field())
	    {
	        $result[$field->name] = NULL;
	        $params[] = &$result[$field->name];
	    }

	    call_user_func_array(array($stmt, 'bind_result'), $params);

	    return $result;
	}

	/**
	 * Returns a copy of an array of references
	 */
	private function getCopy($row)
	{
	    return array_map(function ($a){return $a;}, $row);
	}

//		if (!($stmt = $this->connection->prepare($pSQL))) {
//		     echo "Prepare failed: (" . $this->connection->errno . ") " . $this->connection->error;
//		}

/*
		if (!$stmt->bind_param("s", $this->theTable)) {
		    echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		}
*/

//		if (!$stmt->execute()) {
//		    echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
//		}

//		call_user_func_array(array($stmt, 'bind_result'), $bindArray);
/*
		if (!$stmt -> bind_result($a,$b,$c)) {
		    echo "Bind result failed: (" . $stmt->errno . ") " . $stmt->error;
		}
*/
//		$stmt->store_result();

//		echo "Items: ".$stmt->num_rows."<br>";

//		while ($stmt->fetch()) {
			/*
			printf("%s %s %s<br>\n", $a, $b, $c);
			*/
//			echo $bindArray[0]." ".$bindArray[1]." ".$bindArray[2]." ".$bindArray[3]."<br>";
//    	}

//		$stmt->free_result();
	private function _refValues($arr){
	    if (strnatcmp(phpversion(),'5.3') >= 0) //Reference is required for PHP 5.3+
	    {
	        $refs = array();
	        foreach($arr as $key => $value)
	            $refs[$key] = &$arr[$key];
	        return $refs;
	    }
	    return $arr;
	}

}

?>
