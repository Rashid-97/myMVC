<?php 

	namespace database\components;
	use database\components\DataBase;

	class Oracle_DB extends DataBase{

		private $pr_index = 1; // for prepared statement		
		private $mode = OCI_COMMIT_ON_SUCCESS;

		private $whereCalled = 0;

		public function __construct($server_name) {

			parent::__construct();

			$params = $this->config ["oracle"][$server_name];

			try {
				$this->db = oci_connect($params['user'], $params['password'], $params['host'], $params['character_set']);
			} catch (Exception $e) {
				return false;
			}

		}


		public function query($sql, $params = []) {

			$this->stmt = oci_parse($this->db, $sql);
			if ($this->stmt == false) {
				$this->call_error("Mis Error On Parse");
			}

			$ff = true;
			$result = [];
			if ( is_array($params) && !empty($params) ) {
				foreach ($params as $key => $value) {
					$ff = oci_bind_by_name($this->stmt, ":".$key, $params[$key]);
					if (!$ff) {
						break;
					}
				}
			}
			$this->resetVars();
			$this->pr_index = 1;
			$this->params = [];
			return $ff;
		}

		public function procedureWithCursor($que, $params=[], $fetch_mode="obj") {

			$this->sql = $que;
			if (!empty($params)) {
				$this->params = $params;
			}
			
			$this->stmt = oci_parse($this->db, $this->sql);
			$cursorr = oci_new_cursor($this->db);

			foreach ($this->params as $key => $value) {
				oci_bind_by_name($this->stmt, ":".$key, $this->params[$key] );
			}
        	oci_bind_by_name($this->stmt, ":cursor" ,$cursorr, -1, OCI_B_CURSOR);

        	$data = [];
        	if ( oci_execute($this->stmt) && oci_execute($cursorr) ) {
        		// $flags = OCI_FETCHSTATEMENT_BY_ROW+OCI_ASSOC;
        		// oci_fetch_all($this->stmt, $output, null, null, $flags);
        		// if ($fetch_mode == "obj") {
        			while ($row = oci_fetch_assoc($cursorr) ) {
        				$data [] = $row;
        			}
        		// }
        		return $data;
        	}
        	return false;
		}

		public function procedureWithVar($que, $params=[]) {

			$this->sql = $que;
			if (!empty($params)) {
				$this->params = $params;
			}
			
			$this->stmt = oci_parse($this->db, $this->sql);

			foreach ($this->params as $key => $value) {
				oci_bind_by_name($this->stmt, ":".$key, $this->params[$key] );
			}

        	if ( oci_execute($this->stmt) ) {
        		return true;
        	}
        	return false;
		}

		public function sql_function($que, $params=[]) {

		}

		public function select($db_cols="*") {

			if (func_num_args() == 1) {      // if method arguments number equals one
				if (is_array($db_cols)) {
					$db_cols = implode($db_cols);
				}
			} 
			else if (func_num_args() > 1) {  // if method arguments number more than one
				$func_args = func_get_args();
				$db_cols = implode(",", $func_args);
			}

			$this->sql = "SELECT " .$db_cols. " FROM " .$this->table_name;

			return $this;
		}

		// public function insert($params = []) {
		// 	$this->sql = "INSERT INTO " .$this->table_name. " ( ";

		// 	if (!empty($params)) {
		// 		$this->params = $params;
		// 	}

		// 	foreach ($this->params as $key => $value) {
		// 		$arr1 [] = "?";
		// 	}
		// 	$arr = array_keys($this->params);
		// 	$cols_name = implode(",", $arr);
		// 	$placeholds_name = implode(",", $arr1);

		// 	$this->sql .= $cols_name. " ) VALUES ( " .$placeholds_name. " )";

		// 	// return $this->sql;
		// 	// return $this->params;
		// 	$result = $this->query($this->sql, $this->params);
		// 	if ($result->execute()) {
		// 		if ($this->db->affected_rows) {
		// 			return true;
		// 		}
		// 		return false;
		// 	}
		// 	return false;
		// }

		public function update($params = []) {
			$this->sql = "UPDATE " .$this->table_name. " SET ";

			if (!empty($params)) {
				$this->params = $params;
			}

			foreach ($this->params as $key => $value) {
				$arr [] = $key. "=:".$key.$this->pr_index;
				$this->pr_index++;
			}
			$str = implode(",", $arr);
			$this->sql .= $str;

			return $this;
		}

		// public function delete() { // updates row_status column value to zero
		
		// 	$operator = "=";
		// 	if (func_num_args() == 1) {
		// 		if (is_assoc(func_get_args()[0])) {
		// 			$arr = func_get_args()[0];
		// 			$arr_key = array_keys($arr)[0];
		// 			$this->params [$arr_key] = $arr[$arr_key];
		// 		}
		// 	}
		// 	else if (func_num_args() == 3) {
		// 		$this->table_name = func_get_args()[0]; // table name
		// 		if (is_assoc(func_get_args()[1])) { // for where condition
		// 			$this->params = func_get_args()[1];
		// 			if (in_array(func_get_args()[2], $this->operators_arr)) {
		// 				$operator = func_get_args()[2];
		// 			}
		// 		}
		// 		else {
		// 			 return false;
		// 		}
		// 	} 
		// 	else if (func_num_args() == 4) {
		// 		$this->table_name = func_get_args()[0];
		// 		$this->params [ func_get_args()[1] ] = func_get_args()[3];
		// 		if (in_array(func_get_args()[2], $this->operators_arr)) {
		// 			$operator = func_get_args()[2];
		// 		}
		// 	}

		// 	$this->sql = "UPDATE " .$this->table_name. " SET row_status = 0 WHERE ";
		// 	foreach ($this->params as $key => $value) {
		// 		$this->sql .= $key. $operator. "?";
		// 	}

		// 	// return $this->sql;
		// 	$result = $this->query($this->sql, $this->params);
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
		// 			if (in_array(func_get_args()[2], $this->operators_arr)) {
		// 				if (in_array(func_get_args()[2], $this->operators_arr)) {
		// 					$operator = func_get_args()[2];
		// 				}
		// 			}
		// 		}
		// 	} 
		// 	else if (func_num_args() == 4) {
		// 		$this->table_name = func_get_args()[0];
		// 		$this->params [ func_get_args()[1] ] = func_get_args()[3];
		// 		if (in_array(func_get_args()[2], $this->operators_arr)) {
		// 			if (in_array(func_get_args()[2], $this->operators_arr)) {
		// 				$operator = func_get_args()[2];
		// 			}
		// 		}
		// 	}

		// 	$this->sql = "DELETE FROM " .$this->table_name. " WHERE ";
		// 	foreach ($this->params as $key => $value) {
		// 		$this->sql .= $key. $operator. "?";
		// 	}

		// 	// return $this->sql;
		// 	$result = $this->query($this->sql, $this->params);
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

		// // public function fullJoin($table, $col, $operator, $col2) {

		// // 	if ($this->fullJoinCalled == 0) {
		// // 		$this->sql .= " AS " .$this->table_name;
		// // 	}
		// // 	$this->sql .= " FULL JOIN " .$table. " AS " .$table.
		// // 				  " ON " .$col .$operator .$col2; 

		// //   	$this->fullJoinCalled++;

		// //   	return $this;

		// // }

		public function where($params, $operator="=") {

			$this->sql .= " WHERE ";
			foreach ($params as $key => $value) {
				$arr [] = $key. $operator. ":".$key.$this->pr_index;
				$this->params [$key.$this->pr_index] = $value;
				$this->pr_index++;
			}
			
			$str = implode(",",$arr);
			$this->sql .= $str;

			$this->whereCalled++;
			return $this;
		}

		public function andNotNull($column_name) {
			$this->sql .= " AND ".$column_name. " IS NOT NULL ";
			return $this;
		}
		public function orNotNull($column_name) {
			$this->sql .= " OR ".$column_name. " IS NOT NULL ";
			return $this;
		}

		public function whereIn($column_name, $val_arr) {

			if (is_string($val_arr)) {
				$val_arr = explode(",", $val_arr);
			}

			$this->sql .= " WHERE " .$column_name. " IN (";
			foreach ($val_arr as $key => $vals) {
				$arr [] = ":".$key.$this->pr_index;
				$this->params [$key.$this->pr_index] = $vals;
				$this->pr_index++;
			}
			$keys = implode(",", $arr);
			$this->sql .= $keys. " )";

			return $this;

		}

		public function and($params, $operator="=") {
			
			$this->sql .= " AND ";
			foreach ($params as $key => $value) {
				$arr [] = $key. $operator. ":".$key.$this->pr_index;
				$this->params [$key.$this->pr_index] = $value;
				$this->pr_index++;
			}
			
			$str = implode(" AND ",$arr);
			$this->sql .= $str;

			return $this;
		}

		public function or($params, $operator="=") {
			
			$this->sql .= " OR ";

			foreach ($params as $key => $value) {
				$arr [] = $key. $operator. ":".$key.$this->pr_index;
				$this->params [$key.$this->pr_index] = $value;
				$this->pr_index++;
			}
			
			$str = implode(" OR ",$arr);
			$this->sql .= $str;

			return $this;
		}

		// // public function xor($params1, $params2) {

		// // 	// $cond1 = ($params1[0] .$params1[1]. $params1[2])? 'true': 'false';
		// // 	// $cond2 = ($params2[0] .$params2[1]. $params2[2])? 'true': 'false';
		// // 	// $this->sql .= " WHERE 
		// // 	// 				( ".($cond1). " AND " .!($cond2)." ) 
		// // 	// 				OR 
		// // 	// 				( ".!($cond1). " AND " .($cond2)." ) ";

		// // 	// return $this;

		// // }

		public function limit($num) {
			$condition = " WHERE ";
			if ($this->whereCalled == 1) {
				$condition = " AND ";
			}
			$this->sql .= $condition."ROWNUM <= " .$num;
			return $this;
		}

		public function fetch($fetch_mode="obj") {
			// return $this->sql;
			// return $this->params;

			if ($fetch_mode == "obj") {
				$fetch_method = "oci_fetch_object";
			}
			else if ($fetch_mode == "assoc") {
				$fetch_method = "oci_fetch_assoc";
			}
			$result = $this->query($this->sql, $this->params);
			if ($result) {
				if (oci_execute($this->stmt, $this->mode)) {
					return $fetch_method($this->stmt);
				}
				$this->call_error("Mis Fetch Execute Error");
				// return false;
			}
			// $this->call_error("Bind Param Error!");
			return false;
			// return false;
		}

		public function fetchAll($fetch_mode="assoc", $by="row") {
			// return $this->sql;
			
			$by_ = OCI_FETCHSTATEMENT_BY_ROW;
			if ($by == "column") {
				$by_ = OCI_FETCHSTATEMENT_BY_COLUMN;
			}

			$fetch_mode_ = OCI_ASSOC;
			if ($fetch_mode == "num") {
				$fetch_mode_ = OCI_NUM;
			}

			$flags = $by_+$fetch_mode_;
			$result = $this->query($this->sql, $this->params);
			if ($result) {
				if (oci_execute($this->stmt, $this->mode)) {
					oci_fetch_all($this->stmt, $output, null, null, $flags);
					return $output;
				}
				$this->call_error("Mis Fetch All Execute Error");
				// return false;
			}
			$this->call_error("Mis Bind Error"); // oci_bind_by_name error
			// return false;

		}

		public function run($msg="") {
			// return $this->sql;
			// return $this->params;
			
			$result = $this->query($this->sql, $this->params);
			if ($result) {
				if (oci_execute($this->stmt, $this->mode)) {
					if (oci_num_rows($this->stmt)) {
						return true;
					}
					$this->call_error("Mis Run Affected Rows Error ".$msg);
					// return false;
				}
				// print_r(oci_error($this->stmt));
				$this->call_error("Mis Run Error ".$msg);
				// return false;
			}
			$this->call_error("Mis Error ".$msg); // oci_bind_by_name error
			// return false;
		}

		public function beginTransaction() {
			$this->mode = OCI_NO_AUTO_COMMIT;
			self::$connections_arr ["oracle"][] = $this->db;
			$this->transact_begin = true;
		}
		public function commit() {
			oci_commit($this->db);
		}
		public function rollback() {
			oci_rollback($this->db);
		}

	}


 ?>