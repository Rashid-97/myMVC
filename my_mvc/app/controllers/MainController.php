<?php 
	
	namespace app\controllers;
	use app\core\Controller;

	class MainController extends Controller{

		public function actionIndex() {
			
			$data = $this->model->getData();
			$arr = [
				"data" => $data
			];

			$this->view->render("Main Page", $arr);

		}


	}

 ?>