<?php 

namespace app\core;
use PDO;

abstract class Model {
	
	protected $db;

	private $driver_name = "mysql";    // for pdo class
	private $current_connection = "pdo";
	private $current_pdo_connection_type = "mysql";

	private $class_name = "MySQL";

	public function __construct($server_name="my_pgsql_server") {

		$this->connect_pgsql($server_name);  // DEFAULT
		return $this->db;
	}

	private function connect($class_name, $server_name) {
		$this->class_name = "app\components\database\\".$class_name;
		$this->current_connection = "";
		if (class_exists($this->class_name)) {
			try {
				$this->db = new $this->class_name($server_name);
			} catch (Exception $e) {
				echo $e->getMessage();
				exit;
			}
		}
		else {
			echo "Class does not exist!";
			exit;
		}
	}
	private function connect_pdo($server_name, $driver_name) {
		$class_name = "app\components\database\PDO_DB";
		$this->current_connection = "pdo";
		if (class_exists($class_name)) {
			try {
				$this->db = new $class_name($server_name, $driver_name);
			} catch (Exception $e) {
				echo $e->getMessage();
				exit;
			}
		}
		else {
			echo "Class does not exist!";
			exit;
		}
	}

	public function connect_to_db($server_name) {
		if ($this->current_connection == "") {
			$this->connect($this->class_name, $server_name);
		}
		else if ($this->current_connection == "pdo") {
			$this->connect_pdo($server_name, $this->current_pdo_connection_type);
		}
		return $this->db;
	}

	public function connect_mysql_pdo($server_name) {
		$this->current_pdo_connection_type = "mysql";
		$this->connect_pdo($server_name, "mysql");
		return $this->db;
	}
	public function connect_pgsql_pdo($server_name) {
		$this->current_pdo_connection_type = "pgsql";
		$this->connect_pdo($server_name, "pgsql");
		return $this->db;
	}
	public function connect_mssql_pdo($server_name) {
		$this->current_pdo_connection_type = "mssql";
		$this->connect_pdo($server_name, "mssql");
		return $this->db;
	}
	public function connect_oracle_pdo($server_name) {
		$this->current_pdo_connection_type = "oracle";
		$this->connect_pdo($server_name, "oracle");
		return $this->db;
	}

	public function connect_mysql($server_name) {
		$this->connect("MySQL_DB", $server_name);
		return $this->db;
	}
	public function connect_pgsql($server_name) {
		$this->connect("PgSQL_DB", $server_name);
		return $this->db;
	}
	public function connect_mssql($server_name) {
		$this->connect("MSSQL_DB", $server_name);
		return $this->db;
	}
	public function connect_oracle($server_name) {
		$this->connect("ORACLE_DB", $server_name);
		return $this->db;
	}
	// public function select($db_cols="*") {
	// 	$this->pr_params = [];
	// 	$this->resetVars();

	// 	if (func_num_args() == 1) {      // if method argumets number equals one
	// 		if (is_array($db_cols)) {
	// 			$db_cols = implode($db_cols);
	// 		}
	// 	} 
	// 	else if (func_num_args() > 1) {  // if method argumets number more than one
	// 		$func_args = func_get_args();
	// 		$db_cols = implode(",", $func_args);
	// 	}

	// 	$this->sql = "SELECT " .$db_cols. " FROM " .$this->table_name;

	// 	return $this;
	// }

	// // public function insert($params = []) {
	// // 	$this->sql = "INSERT INTO " .$this->table_name. " ( ";

	// // 	if (!empty($params)) {
	// // 		$this->params = $params;
	// // 	}

	// // 	$this->pr_params = [];
	// // 	$this->plshld_index = 1;
	// // 	foreach ($this->params as $key => $value) {
	// // 		$arr1 [] = ":" .$key .$this->plshld_index;
	// // 		$this->pr_params [$key.$this->plshld_index] = $value;
	// // 		$this->plshld_index++;
	// // 	}
	// // 	$arr = array_keys($this->params);
	// // 	$cols_name = implode(",", $arr);
	// // 	$placeholds_name = implode(",", $arr1);

	// // 	$this->sql .= $cols_name. " ) VALUES ( " .$placeholds_name. " )";

	// // 	$result = $this->db->query($this->sql, $this->pr_params);
	// // 	if ($result->execute()) {
	// // 		return true;
	// // 	}
	// // 	return false;
	// // }

	// public function insert($params = []) {
	// 	$this->sql = "INSERT INTO " .$this->table_name. " ( ";

	// 	if (!empty($params)) {
	// 		$this->params = $params;
	// 	}

	// 	$this->pr_params = [];
	// 	$this->plshld_index = 1;
	// 	foreach ($this->params as $key => $value) {
	// 		$arr1 [] = "?";					// class pdo, mysql and etc ayri ayri
	// 		$this->pr_params [] = $value;
	// 		$this->plshld_index++;
	// 	}
	// 	$arr = array_keys($this->params);
	// 	$cols_name = implode(",", $arr);
	// 	$placeholds_name = implode(",", $arr1);

	// 	$this->sql .= $cols_name. " ) VALUES ( " .$placeholds_name. " )";

	// 	$result = $this->db->query($this->sql, $this->pr_params);
	// 	if ($result->execute()) {
	// 		return true;
	// 	}
	// 	return false;
	// }

	// public function update($params = []) {
	// 	$this->sql = "UPDATE " .$this->table_name. " SET ";

	// 	if (!empty($params)) {
	// 		$this->params = $params;
	// 	}

	// 	$this->pr_params = [];
	// 	$this->plshld_index = 1;
	// 	foreach ($this->params as $key => $value) {
	// 		$arr [] = $key. "=:" .$key .$this->plshld_index;
	// 		$this->pr_params [$key.$this->plshld_index] = $value;
	// 		$this->plshld_index++;
	// 	}
	// 	$str = implode(",", $arr);
	// 	$this->sql .= $str;

	// 	return $this;
	// }

	// public function delete() { // updates row_status column value to zero
	
	// 	$operator = "=";
	// 	if (func_num_args() == 3) {
	// 		$this->table_name = func_get_args()[0]; // table name
	// 		if (is_assoc(func_get_args()[1])) { // for where condition
	// 			$this->params = func_get_args()[1];
	// 			$operator = func_get_args()[2];
	// 		}
	// 	} 
	// 	else if (func_num_args() == 4) {
	// 		$this->table_name = func_get_args()[0];
	// 		$this->params [ func_get_args()[1] ] = func_get_args()[3];
	// 		$operator = func_get_args()[2];
	// 	}

	// 	$this->sql = "UPDATE " .$this->table_name. " SET row_status = 0 WHERE ";
	// 	$this->pr_params = [];
	// 	$this->plshld_index = 1;
	// 	foreach ($this->params as $key => $value) {
	// 		$this->sql .= $key. $operator. ":" .$key.$this->plshld_index;
	// 		$this->pr_params[$key.$this->plshld_index] = $value;
	// 		$this->plshld_index++;
	// 	}

	// 	$result = $this->db->query($this->sql, $this->pr_params);
	// 	if ($result->execute()) {
	// 		return true;
	// 	}
	// 	return false;
	// }

	// public function _delete() { // deletes row from table
		
	// 	$operator = "=";
	// 	if (func_num_args() == 3) {
	// 		$this->table_name = func_get_args()[0]; // table name
	// 		if (is_assoc(func_get_args()[1])) { // for where condition
	// 			$this->params = func_get_args()[1];
	// 			$operator = func_get_args()[2];
	// 		}
	// 	} 
	// 	else if (func_num_args() == 4) {
	// 		$this->table_name = func_get_args()[0];
	// 		$this->params [ func_get_args()[1] ] = func_get_args()[3];
	// 		$operator = func_get_args()[2];
	// 	}

	// 	$this->sql = "DELETE FROM " .$this->table_name. " WHERE ";
	// 	$this->pr_params = [];
	// 	$this->plshld_index = 1;
	// 	foreach ($this->params as $key => $value) {
	// 		$this->sql .= $key. $operator. ":" .$key.$this->plshld_index;
	// 		$this->pr_params[$key.$this->plshld_index] = $value;
	// 		$this->plshld_index++;
	// 	}

	// 	$result = $this->db->query($this->sql, $this->pr_params);
	// 	if ($result->execute()) {
	// 		return true;
	// 	}
	// 	return false;
	// }

	// public function leftJoin($table, $col, $operator, $col2) {

	// 	if ($this->leftJoinCalled == 0) {
	// 		$this->sql .= " AS " .$this->table_name;
	// 	}
	// 	$this->sql .= " LEFT JOIN " .$table. " AS " .$table.
	// 				  " ON " .$col .$operator .$col2; 

	//   	$this->leftJoinCalled++;

	//   	return $this;

	// }

	// public function rightJoin($table, $col, $operator, $col2) {

	// 	if ($this->rightJoinCalled == 0) {
	// 		$this->sql .= " AS " .$this->table_name;
	// 	}
	// 	$this->sql .= " RIGHT JOIN " .$table. " AS " .$table.
	// 				  " ON " .$col .$operator .$col2; 

	//   	$this->rightJoinCalled++;

	//   	return $this;

	// }

	// public function innerJoin($table, $col, $operator, $col2) {

	// 	if ($this->innerJoinCalled == 0) {
	// 		$this->sql .= " AS " .$this->table_name;
	// 	}
	// 	$this->sql .= " INNER JOIN " .$table. " AS " .$table.
	// 				  " ON " .$col .$operator .$col2; 

	//   	$this->innerJoinCalled++;

	//   	return $this;

	// }

	// public function fullJoin($table, $col, $operator, $col2) {

	// 	if ($this->fullJoinCalled == 0) {
	// 		$this->sql .= " AS " .$this->table_name;
	// 	}
	// 	$this->sql .= " FULL JOIN " .$table. " AS " .$table.
	// 				  " ON " .$col .$operator .$col2; 

	//   	$this->fullJoinCalled++;

	//   	return $this;

	// }

	// public function getByID() {
		
	// }

	// public function where($params, $operator="=") {

	// 	$this->sql .= " WHERE ";
	// 	foreach ($params as $key => $value) {
	// 		$plshld = str_replace(".", "", $key);
	// 		$arr [] = $key. $operator. ":" .$plshld .$this->plshld_index;
	// 		$this->pr_params [$plshld.$this->plshld_index] = $value;
	// 		$this->plshld_index++;
	// 	}
		
	// 	$str = implode($arr);
	// 	$this->sql .= $str;

	// 	return $this;
	// }

	// public function whereIn($column_name, $val_arr) {

	// 	$this->sql .= " WHERE " .$column_name. " IN (";
	// 	foreach ($val_arr as $vals) {
	// 		$plshld = str_replace(".", "", $column_name);
	// 		$arr [] = ":" .$plshld. $this->plshld_index;
	// 		$this->pr_params [$plshld.$this->plshld_index] = $vals;
	// 		$this->plshld_index++;
	// 	}
	// 	$keys = implode(",", $arr);
	// 	$this->sql .= $keys. " )";

	// 	return $this;

	// }

	// public function and($params, $operator="=") {
		
	// 	$this->sql .= " AND ";
	// 	foreach ($params as $key => $value) {
	// 		$plshld = str_replace(".", "", $key);
	// 		$arr [] = $key. $operator. ":" .$plshld .$this->plshld_index;
	// 		$this->pr_params [$plshld.$this->plshld_index] = $value;
	// 		$this->plshld_index++;
	// 	}
		
	// 	$str = implode(" AND ",$arr);
	// 	$this->sql .= $str;

	// 	return $this;
	// }

	// public function or($params, $operator="=") {
		
	// 	$this->sql .= " OR ";

	// 	foreach ($params as $key => $value) {
	// 		$plshld = str_replace(".", "", $key);
	// 		$arr [] = $key. $operator. ":" .$plshld .$this->plshld_index;
	// 		$this->pr_params [$plshld.$this->plshld_index] = $value;
	// 		$this->plshld_index++;
	// 	}
		
	// 	$str = implode(" OR ",$arr);
	// 	$this->sql .= $str;

	// 	return $this;
	// }

	// public function xor($params1, $params2) {

	// 	// $cond1 = ($params1[0] .$params1[1]. $params1[2])? 'true': 'false';
	// 	// $cond2 = ($params2[0] .$params2[1]. $params2[2])? 'true': 'false';
	// 	// $this->sql .= " WHERE 
	// 	// 				( ".($cond1). " AND " .!($cond2)." ) 
	// 	// 				OR 
	// 	// 				( ".!($cond1). " AND " .($cond2)." ) ";

	// 	// return $this;

	// }

	// public function limit($num) {
	// 	$this->sql .= " LIMIT " .$num;
	// 	return $this;
	// }

	// public function fetch($fetch_mode="obj") {
	// 	// return $this->sql;

	// 	$result = $this->db->query($this->sql, $this->pr_params);
	// 	if ($result->execute()) {
	// 		return $result->fetch($fetch_mode);
	// 	}
	// 	return false;

	// }

	// public function fetchAll($fetch_mode="obj") {
	// 	// return $this->sql;
		
	// 	$result = $this->db->query($this->sql, $this->pr_params);
	// 	if ($result->execute()) {
	// 		return $result->fetchAll($fetch_mode);
	// 	}
	// 	return false;

	// }

	// public function run() {
	// 	$result = $this->db->query($this->sql, $this->pr_params);
	// 	$result->execute();
		
	// 	return $result;
	// }

	// private function resetVars() {
	// 	$this->leftJoinCalled  = 0;
	// 	$this->rightJoinCalled = 0;
	// 	$this->innerJoinCalled = 0;
	// 	$this->fullJoinCalled  = 0;

	// 	$this->plshld_index = 1;
	// }

}

 ?>