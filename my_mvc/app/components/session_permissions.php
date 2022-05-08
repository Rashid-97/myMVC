<?php 

	function permission($type, $permission_name) {
		if (isset($_SESSION["permissions"])) {

			if (isset($_SESSION["permissions"][$type])) {
				$session_permission = $_SESSION["permissions"][$type];

				if (isset($session_permission [$permission_name])) {
					return $session_permission [$permission_name];
				}

			}
			
		}

	}

 ?>