<?php 
	
	namespace app\models;
	use app\core\Model;

	class Main extends Model{

		public function __construct() {
			parent::__construct();
			$this->table_name = "table_name";
		}

		public function getData() {

			// $this->connect_mysql("my_pgsql_server_pdo"); // example
			// $this->connect_pgsql_pdo("my_pgsql_server_pdo");  // example
			// $this->connect_to_db("my_pgsql_server_pdo"); // example
			// $this->connect_mssql_pdo("my_mssql_server_pdo");  // example

			$pgsql = $this->connect_pgsql("my_pgsql_server");
			$mysql = $this->connect_mysql_pdo("my_server1_pdo");

			$this->db->error_msg_type=2;

			$pgsql->beginTransaction();
			$mysql->beginTransaction();

			$pgsql->table_name = "finance";
			$p_arr = [
				"finname" => "This is fin name"
			];

			$mysql->table_name = "test_table";
			$m_arr = [
				"username" => "New User is Here"
			];
			
			$mysql->insert($m_arr);
			$pgsql->insert($p_arr, "returning idnumber");

			$pgsql->commit();
			
			return [
				$mysql->lastInsertedId("id"),
				$mysql->commit(),
				$pgsql->lastInsertedId()
				// $pgsql->lastInsertedId(),
			];


		}

	}

 ?>