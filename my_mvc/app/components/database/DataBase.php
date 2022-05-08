<?php 

	namespace database\components;

	abstract class DataBase {

		protected $db = null;
		protected $driver_name;
		protected $server_name = null;
		protected $stmt = null;

		protected $operators_arr = ["=", ">", "<", ">=", "<=", "!=", "<=>"];
		public $params = [];
		public $table_name = ''; // needs in model classes
		protected $sql = '';
		public $row_status = "row_status";

		protected $leftJoinCalled  = 0;
		protected $rightJoinCalled = 0;
		protected $innerJoinCalled = 0;
		protected $fullJoinCalled  = 0;

		protected $config;

		public static $connections_arr = [];
		protected $transact_begin = false;
		public $error_msg_type = 2;

		protected $class_props = [];

		protected function __construct() {
			$this->config = require (__DIR__."/../config/db_params.php");
		}

		public function __set($name, $value){
			$this->class_props[$name] = $value;
		}
		public function __get($name){
			if (array_key_exists($name, $this->class_props)) {
				return $class_props[$name];
			}
		}

		protected function resetVars() {
			$this->leftJoinCalled  = 0;
			$this->rightJoinCalled = 0;
			$this->innerJoinCalled = 0;
			$this->fullJoinCalled  = 0;
		}

		/* orm methods START */
		public function sql($sql, $params = []) {

			$this->sql = $sql;
			$this->params = $params;

			return $this;
		}

		public function get_query() {
			return $this->sql;
		}

		public function whereNotNull($column_name) {
			$this->sql .= " WHERE ".$column_name. " IS NOT NULL";
			return $this;
		}
		public function whereNull($column_name) {
			$this->sql .= " WHERE ".$column_name. " IS NULL";
			return $this;
		}
		public function andNotNull($column_name) {
			$this->sql .= " AND ".$column_name. " IS NOT NULL";
			return $this;
		}
		public function andNull($column_name) {
			$this->sql .= " AND ".$column_name. " IS NULL";
			return $this;
		}
		public function andIn($column_name, $values) {
			if (is_array($values)) {
				$values = implode(",", $values);
			}
			$this->sql .= " AND ".$column_name. " IN (".$values.")";
			return $this;
		}
		public function orNotNull($column_name) {
			$this->sql .= " OR ".$column_name. " IS NOT NULL";
			return $this;
		}
		public function orNull($column_name) {
			$this->sql .= " OR ".$column_name. " IS NULL";
			return $this;
		}
		public function orIn($column_name, $values) {
			if (is_array($values)) {
				$values = implode(",", $values);
			}
			$this->sql .= " OR ".$column_name. " IN (".$values.")";
			return $this;
		}
		public function between($column_name, $val1, $val2){
			$this->sql .= " WHERE ".$column_name. " BETWEEN " .$val1. " AND " .$val2;
			return $this;
		}


		public function group_by($col) {
			$this->sql .= " GROUP BY " .$col;
			return $this;
		}
		public function orderBy($col, $sort_type="ASC"){
			$sort_type = strtoupper($sort_type);
			$this->sql .= " ORDER BY " .$col. " " .$sort_type;
			return $this;
		}
		/* orm methods END */

		protected function response($extra_data_arr) {
			$resp_msg ["success"] = true;
			$result = array_merge($resp_msg, $extra_data_arr);

			echo json_encode($result);
			exit;
		}
		protected function call_error($msg) {

			if ($this->transact_begin) {
				$this->rollback_all_connections();
				$this->transact_begin = false;
			}

			if ($this->error_msg_type == 1) {
				echo $msg;
			}
			else if ($this->error_msg_type == 2) {
				echo json_encode([
					"success" => false,
					"warning" => false,
					"msg" => $msg
				]);
			}
			else if ($this->error_msg_type == 3) {
				echo json_encode([
					"success" => false,
					"warning" => true,
					"msg" => $msg
				]);
			}
			exit;
		}

		private function rollback_all_connections() {

			/* example*/
			// $connections_arr = [
			// 	'oracle' => [$oci_conn],
			// 	'pgsql' => [$pg_conn],
			// 	'mysqli' => [$my_conn],
			// 	'pdo' => [$pdo_conn]
			// ];

			foreach (self::$connections_arr as $name => $conn_arr) {
				foreach ($conn_arr as $key => $conn) {
					
					switch ($name) {
						case "pdo":
							$conn->rollback();
							break;

						case "mysqli":
							$conn->rollback();
							break;

						case "pgsql":
							pg_query($conn, "ROLLBACK");
							break;

						case "oracle":
							oci_rollback($conn);
							break;

						case "mssql":
							/* rollback function */
							break;
					}

				}

			}

		}

		protected function convert_arr_to_obj($arr) {
			array_walk($arr, function(&$value, $key){
				$value = (object) $value;
			});
			return $arr;
		}

		protected function is_assoc(array $arr) {
		    if (array() === $arr) return false;
		    return array_keys($arr) !== range(0, count($arr) - 1);
		}

	}

 ?>