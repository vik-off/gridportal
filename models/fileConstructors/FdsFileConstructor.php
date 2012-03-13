<?php

class FdsFileConstructor extends AbstractFileConstructor {

	
	/**
	 * ПОЛУЧИТЬ МАССИВ ДАННЫХ ДЛЯ ОДНОЙ СТРОКИ ДЛЯ ФОРМЫ-КОНСТРУКТОРА
	 * @param string $row - строка из файла
	 * @param integer $rowIndex - номер текущей строки
	 * @return array|null - массив с ключами 
	 *                      'index'          - индекс параметра в общем массиве параметров,
	 *                      'field'          - имя поля,
	 *                      'value'          - строка значения,
	 *                      'allow_multiple' - флаг, можно ли использовать множители
	 *                      или NULL, если строка не должна редактироваться в форме
	 */
	protected function _getFormRow($row, $rowIndex){
		
		$row = trim($row);
		
		$field         = null;
		$preText       = null;
		$value         = null;
		$postText      = null;
		$allowMultiple = null;
		
		/*
		// TIME T_END
		if (preg_match('/(&TIME T_END=)(.*)(\/)/', $row, $matches)) {
			// echo '<pre>'; print_r($matches); die;
			$field         = 'TIME T_END';
			// $field         = Lng::get('file-constructors.fds.T_END');
			$preText       = $matches[1];
			$value         = $matches[2];
			$postText      = $matches[3];
		}
		
		// MISC TMPA
		elseif (preg_match('/(&MISC TMPA=)(.*)(\/)/', $row, $matches)) {
			// echo '<pre>'; print_r($matches); die;
			$field         = 'MISC TMPA';
			// $field         = Lng::get('file-constructors.fds.T_END');
			$preText       = $matches[1];
			$value         = $matches[2];
			$postText      = $matches[3];
		}
		*/
		/*$str = $row;
		$res = array();
		preg_match("/&([A-z0-9_]+)\s+(.*)\//", $str, $res);//(([A-z0-9_]+)=\'?([A-z0-9_]+)\'?,\s)
		//print_r($res);
		
		if (isset($res[1])) $ret[$i]['name'] = $res[1];
		if (isset($res[2])){
			$str = $res[2];
			//preg_match_all("/(?:(?:([\w_]+)=(?:([^,]*)))+)(?:,\s*)?/", $str, $res);
			preg_match_all("/(?:(?:([^,\s]+)=(?:([^,]*)))+)(?:,\s*)?/", $str, $res);
			//print_r($res);
			for ($j = 0; $j < count($res[1]); $j++){
				//$res[1][$j] = preg_replace("/[\W]/", "_", $res[1][$j]);
				$ret[$i]['args'][$res[1][$j]] = $res[2][$j];
			}
		}*/
		
		// отлов множителей
		$value = $this->_getFormValueFromFileValue($value);
		$allowMultiple = is_array($value) || is_numeric(trim($value));
		
		if (0) {
			return array(
				'row'            => $rowIndex,
				'items' => array(
					'field'          => $field,
					'value'          => $value,
					'allow_multiple' => $allowMultiple,
				),
			);
		}
		
		if ($field) {
			return array(
				'row'            => $rowIndex,
				'field'          => $field,
				'pre_text'       => $preText,
				'value'          => $value,
				'post_text'      => $postText,
				'allow_multiple' => $allowMultiple,
			);
		} else {
			return null;
		}
	}
	
	public function getConstructorFormData(){
		
		$modelData = file_get_contents($this->filename);
		$model = array();
		$modelData = preg_replace("/,\s*\n\s*/", ", ", $modelData);
		preg_match_all('/&.+\/.*\n/', $modelData, $model);

		$ret = array();
		for ($i = 0; $i < count($model[0]); $i++){
			
			$str = $model[0][$i];
			$res = array();
			preg_match("/&([A-z0-9_]+)\s+(.*)\//", $str, $res);
			$name = $res[1];
			$args = $res[2];
			
			if (isset($res[1])){
				$ret[$i]['name'] = $name;
			
				if (isset($args)){
					$res = array();
					preg_match_all("/(?:(?:([^,\s]+)=(?:([^,]*)))+)(?:,\s*)?/", $args, $res);
					$argsArray = $res[1];
					$argsValues = $res[2];
					for ($j = 0; $j < count($argsArray); $j++){
						$value = $this->_getFormValueFromFileValue($argsValues[$j]);
						$ret[$i]['args'][$argsArray[$j]] = array(
							'value' => $value,
							'allow_multiple' => is_array($value) || is_numeric(trim($value)),
						);
					}
				}
			}
		}
		
		return $ret;
	}
	
		
	/** СОХРАНИТЬ ДАННЫЕ ИЗ ФОРМЫ-КОНСТРУКТОРА В ФАЙЛ */
	public function saveConstructorFormData($formData, $setInstance = null){
		
		$modelData = file_get_contents($this->filename);
		$model = array();
		$modelData = preg_replace("/,\s*\n\s*/", ", ", $modelData);
		preg_match_all('/&.+\/.*\n/', $modelData, $model);
		
		$ret = array();
		for ($i = 0; $i < count($model[0]); $i++){
			
			$str = $model[0][$i];
			$res = array();
			preg_match("/&([A-z0-9_]+)\s+(.*)\//", $str, $res);
			$name = $res[1];
			$args = $res[2];
			
			if (isset($res[1])){
				$ret[$i]['name'] = $name;
			
				if (isset($args)){
					$res = array();
					preg_match_all("/(?:(?:([^,\s]+)=(?:([^,]*)))+)(?:,\s*)?/", $args, $res);
					$argsArray = $res[1];
					$argsValues = $res[2];
					for ($j = 0; $j < count($argsArray); $j++){
						$value = $this->_getFormValueFromFileValue($argsValues[$j]);
						$ret[$i]['args'][$argsArray[$j]] = array(
							'value' => $value,
							'allow_multiple' => is_array($value) || is_numeric(trim($value)),
						);
					}
				}
			}
		}
		
		echo '<pre>';
		//print_r($_POST);
		//print_r($ret);
		
		foreach ($_POST['keys'] as $i => $a){
			foreach ($a as $k => $v){
				$items = $_POST['items'][intval($v)]['value'];
				if (is_array($items)){
					$res = array();
					foreach ($items as $item){
						if (isset($item['single'])) $res[] = $item['single'];
						else $res[] = $item['from'] . '-' . $item['to'] . ':' . $item['step'];
					}
					$res = implode(',', $res);
				}
				else {
					$res = $items;
				}
				$ret[intval($v)]['args'][$k]['value'] = $res;
			}
		}
		
		//print_r($_POST);
		//print_r($ret);
		
		$content = "";
		foreach ($ret as $item){
			$args = array();
			foreach ($item['args'] as $argn => $argv){
				$args[] = $argn . '=' . $argv;
			}
			$content .= '&' . $item['name'] . ' ' . implode(', ', $args) . "/\n";
		}
		
		print_r($content);
		//file_put_contents($this->filename, $ret);
		
		/*
		$contentArr = file($this->filename);
		foreach($formData as $rowIndex => $data)
			$contentArr[$rowIndex] = $data['pre_text'].$this->parseFormMultiplier($data['value']).$data['post_text']."\n";
		file_put_contents($this->filename, implode('', $contentArr));*/
	}
}

?>