<?php 

	// Front Controller
	
	ini_set('display_errors', 1);
	session_start();
	// define paths
	define('ROOT', dirname(__FILE__));
	define('APP_MODELS', ROOT. '/app/models/');
	define('APP_VIEWS', ROOT. '/app/views/');
	define('APP_CONTROLLERS', ROOT. '/app/controllers/');

	include (ROOT. '/app/components/Autoload.php');
	include (ROOT. '/app/components/Func.php');
	include (ROOT. '/app/components/session_permissions.php');

	use app\components\Router;

	$router = new Router();
	$router->run();

?>