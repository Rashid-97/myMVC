<?php 
	
	namespace app\models;
	use app\core\Model;

	class User extends Model{

		public function __construct() {
			parent::__construct();
			$this->table_name = "users";
		}

		public function findAll() {
			return $this->db->select()->fetchAll();
		}

		public function getUserById($id) {
			return $this->db->select()->where(["id"=>$id])->fetch();
		}

		public function isAdmin() {
			return $this->select("is_admin")->where(["id"=>$_SESSION["user_id"]])->fetch()->is_admin;
		}

	}

 ?>