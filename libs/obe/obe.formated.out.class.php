<?php
class OBE_FOutput{
	public static function table($data){
		$counter = 1;
		$table = '<table>';

		if(!empty($data)){

			$keys = array_keys(reset($data));

			$table .= '<tr><th>#</th><th>key</th><th>'. implode('</th><th>', $keys) . '</th></tr>';

			foreach($data as $k => $r){
				$table .= '<tr><td>' . $counter . '</td><td>' . $k . '</td><td>' . implode('</td><td>', $r) . '</td></tr>';
				$counter++;
			}
		}else{
			$table .= '<tr><td>empty</td></tr>';
		}
		$table .= '</table>';
		echo $table;
	}
}