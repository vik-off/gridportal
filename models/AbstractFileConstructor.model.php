<?php

/**
 * ЗАДАЧИ, РЕШАЕМЫЕ КЛАССОМ
 * 1) сгенерировать форму мастера [ self::getConstructorHTML() ]
 * 2) размножить файл для сабмитов
 * 3) вывести статистику (количество вариаций, и т.д.)
 */

abstract class AbstractFileConstructor {
	
	public $filename = null;
	
	public $multiplierLeftTag = '{*';
	public $multiplierRightTag = '*}';
	
	protected $_fileContent = '';
	
	
	public function __construct($filename){
	
		$this->filename = $filename;
	}
	
	public function getConstructorFormData(){
		
		$formRows = array();
		foreach(file($this->filename) as $index => $row)
			if ($formRow = $this->_getFormRow($row, $index))
				$formRows[] = $formRow;
		
		return $formRows;
	}
	
	public function getMultipliers(){
		
		$multipliers = array();
		foreach (file($this->filename) as $index => $row) {
			if (preg_match_all('/\{\*(.+?)\*\}/', $row, $matches)) {
				foreach ($matches[1] as $mult) {
					$multipliers[] = array(
						'file' => $this->filename,
						'row' => $index,
						'valuesStr' => $mult,
						'values' => $this->parseMultiplier($mult),
					);
				}
			}
		}
		
		return $multipliers;
	}
	
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
	
	public function parseMultiplier($str){
		
		$values = explode(',', $str);
		foreach($values as &$v)
			$v = trim($v);
		
		return $values;
	}
	
	abstract protected function _getFormRow($row, $rowIndex);
}

?>