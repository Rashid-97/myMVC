<?php 

	namespace app\core;

	class View {

		public $path;
		public $route;
		public $layout = 'default';

		public function __construct($route) {
			$this->route = $route;
			$this->path = 'app/views/' .ucfirst($route['controller']). '/' .$route['action']. '.php';
		}

		public function render($title, $vars = []) {
			extract($vars);
			if (file_exists($this->path)) {
				require $this->path;
			}
		}

		public function redirect($url) {
			header("Location:/". $url);
			exit;
		}

		public static function errorCode($code) {
			echo "Error ".$code;
			exit;
		}

	}

 ?>