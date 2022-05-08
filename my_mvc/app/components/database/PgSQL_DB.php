<?php 

	namespace database\components;
	use database\components\DataBase;

	class PgSQL_DB extends DataBase{

		private $stmt_name = "my_query";
		public $params = [];

		private $result; // pg_query ucun olan

		private $pr_index = 1; // for prepared statement

		public $srid = 3857;
		public $epsg = 32639;

		public function __construct($server_name) {

			parent::__construct();

			$params = $this->config ["pgsql"][$server_name];

			try {
				$this->db = pg_connect("host=".$params['host']." port=".$params['port']." dbname=".$params['database']." user=".$params['user']." password=".$params['password']);
			} catch (Exception $e) {
				return false;
			}
		
		}

		public function query($sql, $params = []) {

			// $result = pg_prepare($this->db, $this->stmt_name, $sql);
			// $result = pg_execute($this->db, $this->stmt_name, array_values($params));

			$result = pg_query_params($this->db, $sql, array_values($params));

			$this->pr_index = 1;
			$this->params = [];
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

			$this->sql = "SELECT " .$db_cols. " FROM " .$this->table_name;

			return $this;
		}

		public function insert($params = [], $get_last_insert_id="") {
			$this->sql = "INSERT INTO " .$this->table_name. " ( ";

			if (!empty($params)) {
				$this->params = $params;
			}

			foreach ($this->params as $key => $value) {
				if ($key == "the_geom") {
					$arr1 [] = "st_transform(st_geomfromewkt('SRID=".$this->srid.";POINT('||$".$this->pr_index."||')'),".$this->epsg.")";
				}
				else {
					$arr1 [] = "$".$this->pr_index;
				}
				$this->pr_index++;
			}
			$arr = array_keys($this->params);
			$cols_name = implode(",", $arr);
			$placeholds_name = implode(",", $arr1);

			$this->sql .= $cols_name. " ) VALUES ( " .$placeholds_name. " ) ".$get_last_insert_id;

			// return $this->sql;
			// return $this->params;
			$this->result = $this->query($this->sql, $this->params);
			if ($this->result) {
				if (pg_affected_rows($this->result)){
					return true;
				}
				$this->call_error("Cis Insert Affected Rows Error");
				// return false;
			}
			// return $this->get_error();
			$this->call_error("Cis Insert Execute Error");
			// return false;
		}
		public function insert_get_error($params = [], $get_last_insert_id="") {
			$this->sql = "INSERT INTO " .$this->table_name. " ( ";

			if (!empty($params)) {
				$this->params = $params;
			}

			foreach ($this->params as $key => $value) {
				if ($key == "the_geom") {
					$arr1 [] = "st_transform(st_geomfromewkt('SRID=".$this->srid.";POINT('||$".$this->pr_index."||')'),".$this->epsg.")";
				}
				else {
					$arr1 [] = "$".$this->pr_index;
				}
				$this->pr_index++;
			}
			$arr = array_keys($this->params);
			$cols_name = implode(",", $arr);
			$placeholds_name = implode(",", $arr1);

			$this->sql .= $cols_name. " ) VALUES ( " .$placeholds_name. " ) ".$get_last_insert_id;

			// return $this->sql;
			// return $this->params;
			$this->result = $this->query($this->sql, $this->params);
			return $this->get_error();
		}

		public function update($params = []) {
			$this->sql = "UPDATE " .$this->table_name. " SET ";

			if (!empty($params)) {
				$this->params = $params;
			}

			foreach ($this->params as $key => $value) {
				$arr [] = $key. "=$".$this->pr_index;
				$this->pr_index++;
			}
			$str = implode(",", $arr);
			$this->sql .= $str;

			return $this;
		}

		public function delete() { // updates row_status column value to zero
		
			$operator = "=";
			if (func_num_args() == 1) {
				if ($this->is_assoc(func_get_args()[0])) {
					$arr = func_get_args()[0];
					$arr_key = array_keys($arr)[0];
					$this->params [$arr_key] = $arr[$arr_key];
				}
			}
			else if (func_num_args() == 3) {
				$this->table_name = func_get_args()[0]; // table name
				if ($this->is_assoc(func_get_args()[1])) { // for where condition
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

			$this->sql = "UPDATE " .$this->table_name. " SET ".$this->row_status." = 0 WHERE ";
			foreach ($this->params as $key => $value) {
				$this->sql .= $key. $operator. "$".$this->pr_index;
				$this->pr_index++;
			}

			$result = $this->query($this->sql, $this->params);
			// return $this->sql;
			if ($result) {
				if (pg_affected_rows($result) > 0){
					return true;
				}
				$this->call_error("Cis Delete Affected Rows Error");
				// return false;
			}
			$this->call_error("Cis Delete Execute Error");
			// return false;
		}

		public function _delete() { // deletes row from table
			
			$operator = "=";
			if (func_num_args() == 1) {
				if ($this->is_assoc(func_get_args()[0])) {
					$arr = func_get_args()[0];
					$arr_key = array_keys($arr)[0];
					$this->params [$arr_key] = $arr[$arr_key];
				}
			}
			else if (func_num_args() == 3) {
				$this->table_name = func_get_args()[0]; // table name
				if ($this->is_assoc(func_get_args()[1])) { // for where condition
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

			$this->sql = "DELETE FROM " .$this->table_name. " WHERE ";
			foreach ($this->params as $key => $value) {
				$this->sql .= $key. $operator. "$".$this->pr_index;
				$this->pr_index++;
			}

			$result = $this->query($this->sql, $this->params);
			// return $this->sql;
			if ($result) {
				if (pg_affected_rows($result) > 0){
					return true;
				}
				$this->call_error("Cis _Delete Affected Rows Error");
				// return false;
			}
			$this->call_error("Cis _Delete Execute Error");
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

		public function fullJoin($table, $col, $operator, $col2) {

			if ($this->fullJoinCalled == 0) {
				$this->sql .= " AS " .$this->table_name;
			}
			$this->sql .= " FULL JOIN " .$table. " AS " .$table.
						  " ON " .$col .$operator .$col2; 

		  	$this->fullJoinCalled++;

		  	return $this;

		}

		public function where($params, $operator="=") {

			$this->sql .= " WHERE ";
			foreach ($params as $key => $value) {
				$arr [] = $key. $operator. "$".$this->pr_index;
				$this->pr_index++;
				$this->params [] = $value;
			}
			
			$str = implode(",",$arr);
			$this->sql .= $str;

			return $this;
		}

		public function where_ol_point($params, $col_name="the_geom", $srid=3857, $epsg=32639, $radius=0.2) {

		// ST_Intersects(the_geom, ST_Buffer(ST_Transform(ST_GeomFromEwkt('SRID=3857;POINT('||$1||' '||$2||')'), 32639),0.2))


			$this->sql .= " WHERE ST_Intersects(".$col_name.", ST_Buffer(ST_Transform(ST_GeomFromEwkt('SRID=".$srid.";POINT(";
			foreach ($params as $key => $value) {
				$arr [] = " '||$".$this->pr_index. "||' ";
				$this->pr_index++;
				$this->params [] = $value;
			}
			
			$str = implode(" ",$arr);
			$this->sql .= $str;

			$this->sql .= ")'), ".$epsg."),".$radius."))";
			
			return $this;
		}
		public function and_ol_point($params, $col_name="the_geom", $srid=3857, $epsg=32639, $radius=0.2) {

			$this->sql .= " AND ST_Intersects(".$col_name.", ST_Buffer(ST_Transform(ST_GeomFromEwkt('SRID=".$srid.";POINT(";
			foreach ($params as $key => $value) {
				$arr [] = " '||$".$this->pr_index. "||' ";
				$this->pr_index++;
				$this->params [] = $value;
			}
			
			$str = implode(" ",$arr);
			$this->sql .= $str;

			$this->sql .= ")'), ".$epsg."),".$radius."))";
			
			return $this;
		}
		public function or_ol_point($params, $col_name="the_geom", $srid=3857, $epsg=32639, $radius=0.2) {

			$this->sql .= " OR ST_Intersects(".$col_name.", ST_Buffer(ST_Transform(ST_GeomFromEwkt('SRID=".$srid.";POINT(";
			foreach ($params as $key => $value) {
				$arr [] = " '||$".$this->pr_index. "||' ";
				$this->pr_index++;
				$this->params [] = $value;
			}
			
			$str = implode(" ",$arr);
			$this->sql .= $str;

			$this->sql .= ")'), ".$epsg."),".$radius."))";
			
			return $this;
		}

		public function whereIn($column_name, $val_arr) {

			if (is_string($val_arr)) {
				$val_arr = explode(",", $val_arr);
			}

			$this->sql .= " WHERE " .$column_name. " IN (";
			foreach ($val_arr as $vals) {
				$arr [] = "$".$this->pr_index;
				$this->pr_index++;
				$this->params [] = $vals;
			}
			$keys = implode(",", $arr);
			$this->sql .= $keys. " )";

			return $this;

		}

		public function and($params, $operator="=") {
			
			$this->sql .= " AND ";
			foreach ($params as $key => $value) {
				$arr [] = $key. $operator. "$".$this->pr_index;
				$this->pr_index++;
				$this->params [] = $value;
			}
			
			$str = implode(" AND ",$arr);
			$this->sql .= $str;

			return $this;
		}

		public function or($params, $operator="=") {
			
			$this->sql .= " OR ";

			foreach ($params as $key => $value) {
				$arr [] = $key. $operator. "$".$this->pr_index;
				$this->pr_index++;
				$this->params [$key] = $value;
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
			$this->sql .= " LIMIT " .$num;
			return $this;
		}

		public function fetch($fetch_mode="obj") {
			// return $this->sql;
			// return $this->params;

			if ($fetch_mode == "obj") {
				$fetch_method = "pg_fetch_object";
			}
			else if ($fetch_mode == "assoc") {
				$fetch_method = "pg_fetch_assoc";
			}
			$result = $this->query($this->sql, $this->params);
			if ($result) {
				$data = $fetch_method($result);
				return $data;
			}
			$this->call_error("Fetch Execute Error");
			// return false;
		}

		public function get_error() {
			$result = $this->query($this->sql, $this->params);
			$err = pg_last_error($this->db);
			$this->call_error(" Error ==> ".$err);
		}

		public function fetchAll($fetch_mode="obj") {
			// return $this->sql;
			// return $this->params;
			
			$result = $this->query($this->sql, $this->params);
			if ($result) {
				if ($fetch_mode == "obj") {
					return $this->convert_arr_to_obj(pg_fetch_all($result));
				}
				return pg_fetch_all($result);
			}
			$err = ''; //pg_last_error($this->db);
			$this->call_error("Fetch All Execute Error ".$err);
			// return false;
		}

		public function run($msg="") {
			// return $this->sql;
			// return $this->params;
			$this->result = $this->query($this->sql, $this->params);
			if ($this->result) {
				if (pg_affected_rows($this->result) > 0){
					return true;
				}
				$this->call_error("Cis Run Affected Rows Error".$msg);
				// return false;
			}
			$this->call_error("Cis Run Execute Error".$msg);
			// return false;
		}

		public function beginTransaction() {
			pg_query($this->db, "BEGIN");
			self::$connections_arr ["pgsql"][] = $this->db;
			$this->transact_begin = true;
		}
		public function commit() {
			pg_query($this->db, "COMMIT");
		}
		public function rollback() {
			pg_query($this->db, "ROLLBACK");
		}
		public function lastInsertedId() {
			return pg_fetch_row($this->result)[0];
			$this->result = null;
		}

	}


 ?>