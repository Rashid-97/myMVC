<?php 

	namespace database\config;
	use DateTime;

	class Func {

		public static $table_id;

		public static function makeInfoTable ($thead_arr, $tbody_arr, $caption="", $class_name="table table-bordered table-condensed") {

			$thead_row = "";
			$tbody_row = "";
			$colspann = 1;
			foreach ($thead_arr as $class => $val) {
				$val = explode("&", $val);
				$col_row = ( isset($val[1]) ) ? explode("=", $val[1]) : 1;
				$col_row_name = (is_array($col_row) && isset($col_row[0]) ) ? $col_row[0]:'colspan'; // default colspan=1
				$col_row_text = (is_array($col_row) && isset($col_row[1]) ) ? $col_row[1]:1;
	        	$thead_row .= "<th scope='col' ".$col_row_name."=".$col_row_text." style='text-align:center;'>".$val[0]."</th>";
	        }

	        if (empty($tbody_arr) || $tbody_arr == false) {
	        	$tbody_row = "<tr><td colspan='".(count($thead_arr)+1)."' style='text-align:center;'>Məlumat yoxdur</td></tr>";
	        }
	        else {

		        foreach ($tbody_arr as $key => $val) {
		    		$tbody_row .= "<tr>";
		    		$tbody_row .= "<th scope='row'>".($key+1)."</th>";
		    		foreach ($val as $k => $data) {
		    			// $data = explode(",", $data);
		    			// $rowspann = ( isset($data[1]) ) ? $data[1] : 1;
		    			$rowspann=1;
		    			$tbody_row .= "<td rowspan='".$rowspann."'>".$data."</td>";
		    		}
		    		$tbody_row .= "</tr>";
		    	}

	        }

	    	$caption = ($caption != "") ? "<caption>".$caption."</caption>":"";

			$html_table = "<table class='".$class_name."'>
							  ".$caption."
							  <thead>
							    <tr>
							      <th scope='col'>#</th>
							      ".$thead_row."
							    </tr>
							  </thead>
							  <tbody>
							    ".$tbody_row."
							  </tbody>
							</table>";

			return $html_table;

		}

		public static function makeOneRowInfoTable($arr, $caption="", $class_name="table table-bordered", $excpt_col_arr=[]) {


			
			if (empty($arr) || $arr == false) {
				$html_table .= "Məlumat yoxdur";			
			}
			else {
				
				$caption = ($caption != "") ? "<caption>".$caption."</caption>":"";
				
				$html_table = "<table class='".$class_name."'>
							  ".$caption."
							  <tbody>";

			  	foreach ($arr as $th_data => $td_data) {
			  		if ( !in_array($th_data, $excpt_col_arr) ) {
				  		$html_table .= "<tr>
				  							<th>".$th_data."</th>
				  							<td>".$td_data."</td>
				  						</tr>";
			  		}
			  	}

			}

			$html_table .= "</tbody>
						   </table>";

			return $html_table;

		}

		public static function makeTabsInfoTable($arr) {
			// Example:
			// $arr = [
			// 	[
			// 		'tab_name' => 'Tab1',
			// 		'tab_data' => ['name'=>'Some Name', 'Age'=>213],
			// 		'class' => 'active'
			// 	],
			// 	[
			// 		'tab_name' => 'Tab2',
			// 		'tab_data' => ['name'=>'Some Name 222', 'Age'=>555],
			// 		'class' => '',
			//		'method' => 'makeOneRowInfoTable'
			// 	],
			// ];

			$html_data = '<div>';
			$html_data .= '<ul class="nav nav-pills">';

			$tab_pane = '';
			foreach ($arr as $key => $tabs) {
				if (!isset($tabs['class'])) {
					$tabs['class'] = '';
				}
				$html_data .= '<li class="'.$tabs['class'].'"><a href="#tab_'.($key+1).'" id="bina_info_tab" data-toggle="tab">'.$tabs['tab_name'].'</a></li>';
				$tab_pane .= '<div class="tab-pane '.$tabs['class'].'" id="tab_'.($key+1).'">';
				$tab_pane .= call_user_func_array( array(get_called_class(), $tabs['method']), $tabs['tab_data'] );
				$tab_pane .= '</div>';
			}

            $html_data .= '</ul>';
            $html_data .= '<div class="tab-content clearfix">';
            $html_data .= $tab_pane;
            $html_data .= '</div>';

			$html_data .= '</div>';

			return $html_data;
		}

		public function makeHierarchyInfoTable($thead_arr, $tbody_arr, $caption="", $class_name="table table-bordered table-condensed") {
			$thead_row = "";
			$tbody_row = "";
			$colspann = 1;
			foreach ($thead_arr as $class => $val) {
				$val = explode("&", $val);
				$col_row = ( isset($val[1]) ) ? explode("=", $val[1]) : 1;
				$col_row_name = (is_array($col_row) && isset($col_row[0]) ) ? $col_row[0]:'colspan'; // default colspan=1
				$col_row_text = (is_array($col_row) && isset($col_row[1]) ) ? $col_row[1]:1;
	        	$thead_row .= "<th scope='col' ".$col_row_name."=".$col_row_text." style='text-align:center;'>".$val[0]."</th>";
	        }

	        // $data = [
	        // 	123 => [
	        // 		'row_data' => [...],
	        // 		'children' => [
	        // 			543 => [
	        // 				'row_data' => [],
	        // 				'children' => []
	        // 			],
	        // 			133 => [],
	        // 			311 => [],
	        // 		],
	        // 	],
	        // 	435 => [],
	        // 	678 => [],
	        // ];
	        if (empty($tbody_arr) || $tbody_arr == false) {
	        	$tbody_row = "<tr><td colspan='".(count($thead_arr)+1)."' style='text-align:center;'>Məlumat yoxdur</td></tr>";
	        }
	        else {

	        	$index = 0;
	        	foreach ($tbody_arr as $key => $value) {
        			$index++;
        			$tbody_row .= '<tr id="'.$key.'" class="parent_tr">';
	    			$tbody_row .= "<th scope='row'>".($index)."</th>";
	        		foreach ($value['row_data'] as $k => $val) {
        				$rowspann=1;
	    				$tbody_row .= "<td rowspan='".$rowspann."'>".$val."</td>";
	        		}
        			$tbody_row .= "</tr>";
	        		if (isset($value['children'])) {
	        			if (!empty($value['children'])) {
	        				// $child_thead = array_key_first($value['children']); // php version 7.3+
	        				$child_thead = array_keys($value['children'])[0];
	        				$child_thead = array_keys((array)$value['children'][$child_thead]['row_data']);
	        				$tbody_row .= '<tr id="child_of_'.$key.'" style="display: none;"><td colspan="'.(count($thead_arr)+1).'">';
	        				$tbody_row .= self::makeHierarchyInfoTable($child_thead, $value['children']);
	        				$tbody_row .= '</td></tr>';
	        			}
	        		}
	        	}
	        }

	    	$caption = ($caption != "") ? "<caption>".$caption."</caption>":"";

			$html_table = "<table class='".$class_name."'>
							  ".$caption."
							  <thead>
							    <tr>
							      <th scope='col'>#</th>
							      ".$thead_row."
							    </tr>
							  </thead>
							  <tbody>
							    ".$tbody_row."
							  </tbody>
							</table>";


			return $html_table;
		}

		public static function imageCompress($source, $destination=null, $quality_reduce=0.5, $resolution=[], $size_percent=[]) {

			$info = getimagesize($source);
			$width = $info[0];
			$height = $info[1];
			$quality = null;

			$ext = null;

		    if ($info['mime'] == 'image/jpeg') {
		        $image = imagecreatefromjpeg($source);
		    	$method = "imagejpeg";
		    	$ext = ".jpg";
		    	$quality = 100;
		    }

		    elseif ($info['mime'] == 'image/png') {
		        $image = imagecreatefrompng($source);
		    	$method = "imagepng";
		    	$ext = ".png";
		    	$quality = 9;
		    }

		    if ($size_percent != null) {
		    	$new_width = $width*$size_percent[0];
		    	$new_height = $height*$size_percent[1];
			    $image_p = imagecreatetruecolor($new_width, $new_height);
		    	imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

		    	$image = $image_p;
		    }

		    if ($resolution != null) {
			    imageresolution($image, $resolution[0],$resolution[1]);
		    }
		    
		    if ($destination != null) {
		    	$destination = $destination.$ext;
		    }
		    $quality = $quality*$quality_reduce;
		    ob_start();
		    $img_method = $method($image, $destination, $quality);
			$blob = ob_get_contents();
			ob_end_clean();

			if ($blob != "") {
				
				$im = imagecreatefromstring($blob);
				if ($im !== false) {
					$im_width = imagesx($im);
					$im_height = imagesy($im);
					$blob_size = strlen($blob);
				    return [
				    	'blob' => $blob,
				    	'width' => $im_width,
				    	'height' => $im_height,
				    	'size' => $blob_size
				    ];
				}
				else return false;
			}
			return $img_method;
		}

		public static function check_img_format($img_name) {
			$allowed_exts = ["jpg", "jpeg", "png", "JPG", "JPEG", "PNG"];
			$ext = explode(".", $img_name)[1];

			if (!in_array($ext, $allowed_exts)) {
				echo json_encode([
                    'success' => false,
                    'msg' => 'Seçilmiş fayl(-lar) şəkil formatına uyğun deyil! (Şəkil formatları: jpg, jpeg, png)'
                ]);
                exit;
				// return false;
			}
			return true;
		}

		public static function date_format($date, $format="Y-m-d"){
			$createDate = new DateTime($date);
            $strip_date = $createDate->format($format);

            return $strip_date;
		}

		public static function compare_dates($date1_str, $operator, $date2_str, $err_msg) {
			$date1_str = strtotime($date1_str);
			$date2_str = strtotime($date2_str);
			$ff = false;

			switch ($operator) {
				case '>':
					$ff = $date1_str > $date2_str;
					break;
				case '<':
					$ff = $date1_str < $date2_str;
					break;
				case '>=':
					$ff = $date1_str >= $date2_str;
					break;
				case '<=':
					$ff = $date1_str <= $date2_str;
					break;
				case '==':
					$ff = $date1_str == $date2_str;
					break;

				default:
					$ff = $date1_str == $date2_str;
					break;
			}

			if ($ff){
				return true;
			}
			echo json_encode([
				"success" => false,
				"msg" => $err_msg
			]);
			exit;
		}

		public static function combo_box($data, $properties=null) {
			$data = self::convert_arr_to_obj($data);
			
			$name = null;
			$id = null;
			$placeholder = null;
			$class = null;

			if ($properties != null) {
				$prop_arr = explode(",", $properties);
				foreach ($prop_arr as $key => $value) {
					$exp = explode("=", $value);
					// $props [$exp[0]] = $exp[1];
					$props [trim($exp[0])] = trim($exp[1]);
				}
				$name = isset($props["name"]) ? "name='".$props["name"]."'":null;
				$id = isset($props["id"]) ? "id='".$props["id"]."'":null;
				$placeholder = isset($props["placeholder"]) ? "<option value='' selected disabled>".$props["placeholder"]."</option>":null;
				$class = isset($props["class"]) ? "class='".$props["class"]."'":null;
				$required = isset($props["required"]) ? "required='".$props["required"]."'":null;
				$disabled = isset($props["disabled"]) ? "disabled='".$props["disabled"]."'":null;
				$selected_id = isset($props["selected_id"]) ? $props["selected_id"]:null;
			}

			$combo_box = "<select ".$name." ".$class." ".$id." ".$required." ".$disabled.">".$placeholder;
			foreach ($data as $key => $value) {
				$selected_html = "";
				if ($value->id == $selected_id) {
					$selected_html = "selected";
				}
				$combo_box .= "<option value='".$value->id."' ".$selected_html.">".$value->text."</option>";
			}
			$combo_box .= "</select>";
			return $combo_box;
		}

		private static function convert_arr_to_obj($arr) {
			array_walk($arr, function(&$value, $key){
				$value = (object) $value;
			});
			return $arr;
		}


		public static function excel_export($data) {
			echo __DIR__;exit;
			require (dirname(__DIR__, 2)."\phpexcel\Classes\PHPExcel.php");
			require (dirname(__DIR__, 2)."\phpexcel\Classes\PHPExcel\Writer\Excel2007.php");
			$objPHPExcel = new PHPExcel();

			$colnum=1;

			$objPHPExcel->setActiveSheetIndex(0);

			$col_size = count(array_keys($data[0]));
			$char_val = 65; // 'A' character code

			foreach ($data[0] as $col => $val) {
				$char = chr($char_val);
				$objPHPExcel->getActiveSheet()->getColumnDimension($char)->setAutoSize(true);
				$objPHPExcel->getActiveSheet()->SetCellValue($char.'1', $col);

				$char_val++;
			}

			$objPHPExcel->getActiveSheet()->getStyle('A1:'.chr($char_val-1).'1')->getFont()->setBold(true);
			$objPHPExcel->getActiveSheet()->getStyle('A1:'.chr($char_val-1).'1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$objPHPExcel->getActiveSheet()->getStyle('A:'.chr($char_val-1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

			foreach ($data as $arr) 
            {
                $char_val = 65; // 'A' character code
                $colnum++;
                foreach ($arr as $col => $val) {
                    $char = chr($char_val);
                    $objPHPExcel->getActiveSheet()->SetCellValue($char.$colnum, $val);
                    $char_val++;
                }
            }

			Excel($file_name,$objPHPExcel);
		}

	}


 ?>