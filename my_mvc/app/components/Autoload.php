<?php 

	spl_autoload_register(function($class) {
	    $path = str_replace('\\', '/', $class.'.php');
	    if (file_exists($path)) {
	        require $path;
	    }
	});

// 	spl_autoload_register(function($class_name){
		
// 		$array_paths = [
// 		'/app/models/',
// 		'/app/controller/'];

// 		foreach ($array_paths as $paths) {

// 			$path = ROOT. $paths. $class_name. '.php';
// 			if (file_exists($path)) {
// 				require $path;
// 			}
// 		}
// 	}
// );

 ?>