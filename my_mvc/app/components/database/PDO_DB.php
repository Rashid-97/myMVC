<?php 

	namespace database\components;
	use database\components\DataBase;
	use PDO;


	class PDO_DB extends DataBase{
		
		public function __construct($server_name, $driver_name) {

			parent::__construct();
			
			$this->driver_name = $driver_name;
			$params = $this->config ["pdo"][$server_name];

			try {
				if ($driver_name == "mysql") {
					$this->db = new PDO('mysql:host='.$params['host'].';dbname='.$params['database'].'', $params['user'], $params['password']);

				}
				else if ($driver_name == "pgsql") {
					$this->db = new PDO('pgsql:host='.$params['host'].';dbname='.$params['database'].'', $params['user'], $params['password']);
				}
				else if ($driver_name == "mssql") {
					$this->db = new PDO('dblib:version=7.0;charset=UTF-8;host='.$params['host'].';dbname='.$params['database'].'', $params['user'], $params['password']);
				}
				else if ($driver_name == "oracle") {
					// connection
				}
			} catch (Exception $e) {
				return false;
			}

		}


		public function query($sql, $params = []) {

			$result = $this->db->prepare($sql);
			$indexx = 0;
			$type = null;
			if (!empty($params)) {
				foreach ($params as $key => $val) {
					if (is_int($val)) {
						$type = PDO::PARAM_INT;
					}
					else if (is_string($val)) {
						$type = PDO::PARAM_STR;
					}
					else {

					}
					$indexx++;
					if ($type != null) {
						$result->bindValue($indexx, $val, $type);
					}
					else {
						$result->bindValue($indexx, $val);
					}
				}
			}
			// $result->execute();
			return $result;
		}

		public function select($db_cols="*") {
			$this->params = [];
			$this->resetVars();

			if (func_num_args() == 1) {      // if method arguments number equals one
				if (is_array($db_cols)) {
					$db_cols = implode($db_cols);
				}
			} 
			else if (func_num_args() > 1) {  // if method arguments number more than one
				$func_args = func_get_args();
				$db_cols = implode(",", $func_args);
			}

			$this->table_name = ($this->driver_name == "mssql") ? 
											"[dbo][".$this->table_name."]": $this->table_name;

			$this->sql = "SELECT " .$db_cols. " FROM " .$this->table_name;

			return $this;
		}

		public function insert($params = []) {
			$this->sql = "INSERT INTO " .$this->table_name. " ( ";

			if (!empty($params)) {
				$this->params = $params;
			}

			foreach ($this->params as $key => $value) {
				$arr1 [] = "?";
			}
			$arr = array_keys($this->params);
			$cols_name = implode(",", $arr);
			$placeholds_name = implode(",", $arr1);

			$this->sql .= $cols_name. " ) VALUES ( " .$placeholds_name. " )";

			// return $this->sql;
			// return $this->params;
			$result = $this->query($this->sql, $this->params);
			if ($result->execute()) {
				if ($result->rowCount()) {
					return true;
				}
				$this->call_error("Pdo Insert Affected Rows Error");
				// return false;
			}
			$this->call_error("Pdo Insert Execute Error");
			// return false;
		}

		public function update($params = []) {
			$this->sql = "UPDATE " .$this->table_name. " SET ";

			if (!empty($params)) {
				$this->params = $params;
			}

			$this->pr_params = [];
			foreach ($this->params as $key => $value) {
				$arr [] = $key. "=? ";
			}
			$str = implode(",", $arr);
			$this->sql .= $str;

			return $this;
		}

		public function delete() { // updates row_status column value to zero
		
			$operator = "=";
			if (func_num_args() == 1) {
				if (is_assoc(func_get_args()[0])) {
					$arr = func_get_args()[0];
					$arr_key = array_keys($arr)[0];
					$this->params [$arr_key] = $arr[$arr_key];
				}
			}
			else if (func_num_args() == 3) {
				$this->table_name = func_get_args()[0]; // table name
				if (is_assoc(func_get_args()[1])) { // for where condition
					$this->params = func_get_args()[1];
					if (in_array(func_get_args()[2], $this->operators_arr)) {
						$operator = func_get_args()[2];
					}
				}
				else {
					 return false;
				}
			} 
			else if (func_num_args() == 4) {
				$this->table_name = func_get_args()[0];
				$this->params [ func_get_args()[1] ] = func_get_args()[3];
				if (in_array(func_get_args()[2], $this->operators_arr)) {
					$operator = func_get_args()[2];
				}
			}

			$this->sql = "UPDATE " .$this->table_name. " SET row_status = 0 WHERE ";
			foreach ($this->params as $key => $value) {
				$this->sql .= $key. $operator. "?";
			}

			// return $this->sql;
			$result = $this->query($this->sql, $this->params);
			if ($result->execute()) {
				if ($result->rowCount()) {
					return true;
				}
				$this->call_error("Pdo Delete Affected Rows Error");
				// return false;
			}
			$this->call_error("Pdo Delete Execute Error");
			// return false;
		}

		public function _delete() { // deletes row from table

			$operator = "=";
			if (func_num_args() == 1) {
				if (is_assoc(func_get_args()[0])) {
					$arr = func_get_args()[0];
					$arr_key = array_keys($arr)[0];
					$this->params [$arr_key] = $arr[$arr_key];
				}
			}
			else if (func_num_args() == 3) {
				$this->table_name = func_get_args()[0]; // table name
				if (is_assoc(func_get_args()[1])) { // for where condition
					$this->params = func_get_args()[1];
					if (in_array(func_get_args()[2], $this->operators_arr)) {
						$operator = func_get_args()[2];
					}
				}
			} 
			else if (func_num_args() == 4) {
				$this->table_name = func_get_args()[0];
				$this->params [ func_get_args()[1] ] = func_get_args()[3];
				if (in_array(func_get_args()[2], $this->operators_arr)) {
					$operator = func_get_args()[2];
				}
			}
			else {
				return "Wrong using of method";
			}

			$this->sql = "DELETE FROM " .$this->table_name. " WHERE ";
			foreach ($this->params as $key => $value) {
				$this->sql .= $key. $operator. "?";
			}

			// return $this->sql;
			$result = $this->query($this->sql, $this->params);
			if ($result->execute()) {
				if ($result->rowCount()) {
					return true;
				}
				$this->call_error("Pdo _Delete Affected Rows Error");
				// return false;
			}
			$this->call_error("Pdo _Delete Execute Error");
			// return false;
		}

		public function leftJoin($table, $col, $operator, $col2) {

			if ($this->leftJoinCalled == 0) {
				$this->sql .= " AS " .$this->table_name;
			}
			$this->sql .= " LEFT JOIN " .$table. " AS " .$table.
						  " ON " .$col .$operator .$col2; 

		  	$this->leftJoinCalled++;

		  	return $this;

		}

		public function rightJoin($table, $col, $operator, $col2) {

			if ($this->rightJoinCalled == 0) {
				$this->sql .= " AS " .$this->table_name;
			}
			$this->sql .= " RIGHT JOIN " .$table. " AS " .$table.
						  " ON " .$col .$operator .$col2; 

		  	$this->rightJoinCalled++;

		  	return $this;

		}

		public function innerJoin($table, $col, $operator, $col2) {

			if ($this->innerJoinCalled == 0) {
				$this->sql .= " AS " .$this->table_name;
			}
			$this->sql .= " INNER JOIN " .$table. " AS " .$table.
						  " ON " .$col .$operator .$col2; 

		  	$this->innerJoinCalled++;

		  	return $this;

		}

		// public function fullJoin($table, $col, $operator, $col2) {

		// 	if ($this->fullJoinCalled == 0) {
		// 		$this->sql .= " AS " .$this->table_name;
		// 	}
		// 	$this->sql .= " FULL JOIN " .$table. " AS " .$table.
		// 				  " ON " .$col .$operator .$col2; 

		//   	$this->fullJoinCalled++;

		//   	return $this;

		// }

		public function where($params, $operator="=") {

			$this->sql .= " WHERE ";
			foreach ($params as $key => $value) {
				$arr [] = $key. $operator. "?";
				$this->params [$key] = $value;
			}
			
			$str = implode(",",$arr);
			$this->sql .= $str;

			return $this;
		}

		public function whereIn($column_name, $val_arr) {

			if (is_string($val_arr)) {
				$val_arr = explode(",", $val_arr);
			}

			$this->sql .= " WHERE " .$column_name. " IN (";
			foreach ($val_arr as $vals) {
				$arr [] = "?";
				$this->params [array_search($vals, $val_arr)] = $vals;
			}
			$keys = implode(",", $arr);
			$this->sql .= $keys. " )";

			return $this;

		}

		public function and($params, $operator="=") {
			
			$this->sql .= " AND ";
			foreach ($params as $key => $value) {
				$arr [] = $key. $operator. "?";
				$this->params [$key] = $value;
			}
			
			$str = implode(" AND ",$arr);
			$this->sql .= $str;

			return $this;
		}

		public function or($params, $operator="=") {
			
			$this->sql .= " OR ";

			foreach ($params as $key => $value) {
				$arr [] = $key. $operator. "?";
				$this->params [$key] = $value;
			}
			
			$str = implode(" OR ",$arr);
			$this->sql .= $str;

			return $this;
		}

		// public function xor($params1, $params2) {

		// 	// $cond1 = ($params1[0] .$params1[1]. $params1[2])? 'true': 'false';
		// 	// $cond2 = ($params2[0] .$params2[1]. $params2[2])? 'true': 'false';
		// 	// $this->sql .= " WHERE 
		// 	// 				( ".($cond1). " AND " .!($cond2)." ) 
		// 	// 				OR 
		// 	// 				( ".!($cond1). " AND " .($cond2)." ) ";

		// 	// return $this;

		// }

		public function group_by($col) {
			$this->sql .= " GROUP BY " .$col;
			return $this;
		}
		public function limit($num) {
			$this->sql .= " LIMIT " .$num;
			return $this;
		}
		public function rownum($num, $operator) {		// for oracle
			$this->sql .= " ROWNUM " .$operator .$num;
			return $this;
		}
		public function orderBy($col, $sort_type="ASC"){
			$sort_type = strtoupper($sort_type);
			$this->sql .= " ORDER BY " .$col. " " .$sort_type;
			return $this;
		}

		public function fetch($fetch_mode="obj") {
			// return $this->sql;
			// return $this->params;

			if ($fetch_mode == "obj") {
				$fetch_mode = PDO::FETCH_OBJ;
			}
			else if ($fetch_mode == "assoc") {
				$fetch_mode = PDO::FETCH_ASSOC;
			}
			$result = $this->query($this->sql, $this->params);
			if ($result->execute()) {
				return $result->fetch($fetch_mode);
			}
			$this->call_error("Fetch Execute Error");
			// return false;

		}

		public function fetchAll($fetch_mode="obj") {
			// return $this->sql;
			// return $this->params;
			
			if ($fetch_mode == "obj") {
				$fetch_mode = PDO::FETCH_OBJ;
			}
			else if ($fetch_mode == "assoc") {
				$fetch_mode = PDO::FETCH_ASSOC;
			}
			$result = $this->query($this->sql, $this->params);
			if ($result->execute()) {
				return $result->fetchAll($fetch_mode);
			}
			$this->call_error("Fetch All Execute Error");
			// return false;

		}

		public function run() {
			// return $this->sql;
			// return $this->params;
			$result = $this->query($this->sql, $this->params);
			if ($result->execute()) {
				if ($result->rowCount()) {
					return true;
				}
				$this->call_error("Pdo Run Affected Rows Error");
				// return false;
			}
			$this->call_error("Pdo Run Execute Error");
			// return false;
		}

		public function row($sql, $params = []) {
			$result = $this->query($sql, $params);
			return $result->fetchAll(PDO::FETCH_ASSOC);
		}

		public function column($sql, $params = []) {
			$result = $this->query($sql, $params);
			return $result->fetchColumn();
		}

		public function lastInsertedId() {
			return $this->db->lastInsertId();
		}

		public function beginTransaction() {
			return $this->db->beginTransaction();
			$this->connections_arr ["pdo"][] = $this->db;
			$this->transact_begin = true;
		}
		public function commit() {
			return $this->db->commit();
		}
		public function rollback() {
			return $this->db->rollback();
		}
		
	}


 ?>