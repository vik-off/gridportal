<?php

/**
 * ЗАДАЧИ, РЕШАЕМЫЕ КЛАССОМ
 * 1) сгенерировать форму мастера [ self::getConstructorFormData() ]
 * 2) размножить файл для сабмитов
 * 3) вывести статистику (количество вариаций, и т.д.)
 */

abstract class AbstractFileConstructor {
	
	public $filename = null;
	public $basename = null;
	
	public $multiplierLeftTag = '{*';
	public $multiplierRightTag = '*}';
	
	protected $_fileContent = '';
	
	
	public function __construct($filename){
	
		$this->filename = $filename;
		$this->basename = basename($this->filename);
	}
	
	/** ПОЛУЧИТЬ ДАННЫЕ ИЗ ФАЙЛА ДЛЯ ФОРМЫ-КОНСТРУКТОРА */
	public function getConstructorFormData(){
		
		$formRows = array();
		foreach(file($this->filename) as $index => $row)
			if ($formRow = $this->_getFormRow($row, $index)) {
				$formRows[] = $formRow;
			}
		
		return $formRows;
	}
	
	/** СОХРАНИТЬ ДАННЫЕ ИЗ ФОРМЫ-КОНСТРУКТОРА В ФАЙЛ */
	public function saveConstructorFormData($formData, $setInstance = null){
		
		$contentArr = file($this->filename);
		foreach($formData as $rowIndex => $data)
			$contentArr[$rowIndex] = $data['pre_text'].$this->parseFormMultiplier($data['value']).$data['post_text']."\n";
		file_put_contents($this->filename, implode('', $contentArr));
	}
	
	/**
	 * ПОЛУЧИТЬ МАССИВ МНОЖИТЕЛЕЙ ИЗ ФАЙЛА
	 * функция резолвит интервалы в множителях в обычный набор значений
	 */
	public function getMultipliers(){
		
		$multipliers = array();
		$rowsArr = file($this->filename);
		if ($rowsArr) {
			foreach ($rowsArr as $index => $row) {
				if (preg_match_all('/\{\*(.+?)\*\}/', $row, $matches)) {
					foreach ($matches[1] as $mult) {
						$multipliers[] = array(
							'file' => $this->basename,
							'row' => $index,
							'valuesStr' => $mult,
							'values' => $this->resolveMultiplier($this->parseStrMultiplier($mult)),
						);
					}
				}
			}
		}
		
		// echo '<pre>'; print_r($multipliers); echo '</pre><hr />';
		return $multipliers;
	}
	
	/** ПОЛУЧИТЬ СОДЕРЖИМОЕ ФАЙЛА С ПРИМЕНЕННОЙ КОМБИНАЦИЕЙ */
	public function getCombination($values){
		$fileArr = file($this->filename);
		foreach ($values as $v) {
			if (isset($fileArr[ $v['row'] ]))
				$fileArr[ $v['row'] ] = str_replace(
					$this->multiplierLeftTag.$v['valuesStr'].$this->multiplierRightTag,
					$v['value'],
					$fileArr[ $v['row'] ]
				);
			else
				throw new Exception('Файл '.$this->filename.' не содержит строки #'.$v['row']);
		}
		
		return implode("", $fileArr)."\n";
	}
	
	/**
	 * ПРЕОБРАЗОВАТЬ МНОЖИТЕЛЬ В СПИСОК ОДИНОЧНЫХ ЗНАЧЕНИЙ
	 * @param array $mult - массив, содержащий множитель, вида array(1, 2, array('from' => 3, 'to' => 5, 'step' => 1))
	 * @return array - преобразованный массив array(1,2,3,4,5)
	 */
	public function resolveMultiplier($mult){
		
		$resolvedMult = array();
		
		foreach ($mult as &$val) {
			if (is_array($val)) {
				$val['step'] = abs($val['step']);
				if ($val['to'] > $val['from'])
					for ($i = $val['from']; $i <= $val['to']; $i += $val['step'])
						$resolvedMult[] = $i;
				else
					for ($i = $val['from']; $i >= $val['to']; $i -= $val['step'])
						$resolvedMult[] = $i;
			} else {
				$resolvedMult[] = $val;
			}
		}
	
		return $resolvedMult;
	}
	
	/**
	 * РАСПАРСИТЬ СТРОКУ МНОЖИТЕЛЯ В МАССИВ
	 * @param string $str - строка, вида "1,2,3;5:1,6"
	 * @return array - массив, вида array(1, 2, array('from' => 3, 'to' => 5, 'step' => 1), 6)
	 */
	public function parseStrMultiplier($str){
		
		$values = explode(',', $str);
		foreach ($values as &$v) {
			// интервал
			if (strpos($v, ';') !== FALSE) {
				list($intervalStr, $step) = explode(':', $v) + array('', 0); // отделение шага
				$interval = explode(';', $intervalStr) + array(0, 0);
				$v = array('from' => $interval[0], 'to' => $interval[1], 'step' => $step);
			}
			// одиночное значение
			else {
				$v = trim($v);
			}
		}
		return $values;
	}
	
	/**
	 * РАСПАРСИТЬ ЭЛЕМЕНТ ФОРМЫ-КОНСТРУКТОРА В СТРОКУ (парсит множители)
	 * Получает $value из формы констркутора и возвращает строку,
	 * пригодную для сохранения в файл. 
	 */
	public function parseFormMultiplier($value){
		
		if (is_array($value)) {
			$combinations = array();
			$hasIntervals = false;
			
			foreach ($value as $val) {
				
				// одиночное значение
				if (isset($val['single'])) {
					$curVal = $this->normalizeFormValue($val['single']);
					if (!strlen($curVal))
						continue;
				}
				// интервал
				else {
					$from =  $this->normalizeFormValue(getVar($val['from']));
					$to =  $this->normalizeFormValue(getVar($val['to']));
					$step =  $this->normalizeFormValue(getVar($val['step']));
					
					if (!strlen($from) || !strlen($to))
						continue;
					
					$curVal = $from.';'.$to.':'.$step;
					$hasIntervals = true;
				}
				
				// сохраняем комбинацию
				$combinations[] = $curVal;
			}
			
			return !$hasIntervals && count($combinations) == 1
				? $combinations[0]
				: '{*'.implode(',', $combinations).'*}';
				
		} else {
			return $value;
		}
	}
	
	/**
	 * НОРМАЛИЗОВАТЬ ЗНАЧЕНИЕ ФОРМЫ
	 * (запятую на точку, и т.д)
	 * @param string $value - значение множителя
	 * @return string - нормализованное значение множителя
	 */
	public function normalizeFormValue($value) {
		
		$value = trim($value);
		
		// для чисел заменим запятую на точку
		if (preg_match('/^\d*,\d+$/', $value))
			$value = str_replace(',', '.', $value);
			
		return $value;
	}
	
	/**
	 * ПОЛУЧИТЬ МАССИВ ДАННЫХ ДЛЯ ОДНОЙ СТРОКИ ДЛЯ ФОРМЫ-КОНСТРУКТОРА
	 * @param string $row - строка из файла
	 * @param integer $rowIndex - номер текущей строки
	 * @return array|null - массив с ключами 
	 *                      'row'            - номер строки,
	 *                      'field'          - имя поля,
	 *                      'pre_text'       - текст, предшествующий строке значения,
	 *                      'value'          - строка значения,
	 *                      'post_text'      - текст, идущий после строки значения,
	 *                      'allow_multiple' - флаг, можно ли использовать множители
	 *                      или NULL, если строка не должна редактироваться в форме
	 */
	abstract protected function _getFormRow($row, $rowIndex);
	
	/**
	 * ПОЛУЧИТЬ ЗНАЧЕНИЕ ПАРАМЕТРА ДЛЯ ФОРМЫ (отлов множителей)
	 * из значения, полученного из файла.
	 * Если значение из файла - обычный текст, функция вернет его же,
	 * если значение - плейсхолдер, функция распарсит его и вернет массив
	 * @param string $strFileValue - строковое значение, прочитанное из файла
	 * @return string|array - строка или массив для формы
	 */
	protected function _getFormValueFromFileValue($strFileValue){
			
		return preg_match('/^\{\*(.+)\*\}$/', $strFileValue, $matches)
			? $this->parseStrMultiplier($matches[1])
			: $strFileValue;
	}
	
}

?>