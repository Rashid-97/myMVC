<?php 
	
namespace app\core;
use app\core\View;

abstract class Controller {

	public $route;
	public $view;
	private $acl;

	public function __construct($route) {

		$this->route = $route;
		$this->view = new View($route);
		// if (!$this->checkLogged()) {
		// 	$this->view->redirect('user/login');
		// }
		$this->model = $this->loadModel($route['controller']);

	}

	public function loadModel($path) {
		$model = 'app\models\\' .ucfirst($path);

		if (class_exists($model)) {
			return new $model;
		}

	}

	private function checkLogged() {
		$this->acl = require (ROOT. "/app/acl/acl.php");

		if ($this->is_acl('all')) {
			return true;
		}
		else if (isset($_SESSION["user_id"])) {
			return true;
		}
		return false;
	}

	private function is_acl($key) {
		return in_array($this->route['controller'].'/'.$this->route['action'], $this->acl[$key]);
	}
	
}

 ?>
