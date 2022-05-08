<?php 

namespace app\components;
use app\core\View;

class Router {

	private $uri;
	private $routes;
	private $params = [];
	public function __construct() {

		$this->routes = require (ROOT. '/app/config/routes.php');
		$this->uri = trim($_SERVER['REQUEST_URI'], '/');

	}
	private function match() {
		foreach ($this->routes as $route => $path) {
			$arr [$path["url"]] = "/" .$route;
			$GLOBALS ["urls"] = $arr;
		}
		foreach ($this->routes as $route => $path) {

	        $route = preg_replace('/{([a-z]+):([^\}]+)}/', '(?P<\1>\2)', $route);
			$route = '#^'.$route.'$#';
			if (preg_match($route, $this->uri, $matches)) {
				foreach ($matches as $key => $match) {
                    if (is_string($key)) {
                        if (is_numeric($match)) {
                            $match = (int) $match;
                        }
                        $path[$key] = $match;
                    }
                }
				$this->params = $path;
				return true;
			}
		}
			return false;		
	}

	public function run () {

		if ($this->match()) {
			$controllerName = 'app\controllers\\'.ucfirst($this->params['controller']). 'Controller';
			if (class_exists($controllerName)) {
					$action = 'action'. ucfirst($this->params['action']);
					if (method_exists($controllerName, $action)) {
						
						$controller = new $controllerName($this->params);
						$controller->$action();
					
					} else {
						View::errorCode(404);
					}
				} else {
					View::errorCode(405);
				}
		} else {
			View::errorCode(406);
		}
	
	}

}

 ?>