<?

class Request{
	
	private static $_instance = null;
	
	private $_requestString = '';
	private $_requestArr = array();
	
	private $_language = null;
	private $_controller = null;
	private $_display = null;
	private $_params = null;
	
	// ТОЧКА ВХОДА В КЛАСС
	public static function get(){
		
		if(is_null(self::$_instance))
			self::$_instance = new Request(isset($_GET['r']) ? $_GET['r'] : '');
		
		return self::$_instance;
	}
	
	// КОНСТРУКТОР
	private function __construct($requestString){
		
		// hack to prevent display, if favicon.ico requested
		if($requestString == 'favicon.ico'){
			header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); // 'HTTP/1.1 404 Not Found'
			exit;
		}
		
		$this->_requestString = $requestString;
		$this->parseRequest();
	}
	
	// РАЗБОР URL ЗАПРОСА
	public function parseRequest(){
		
		$this->_requestArr = $_rArr = YArray::trim(explode('/', $this->_requestString));
		
		// очистка от языка
		array_shift($this->_requestArr);
		
		// $this->modifyRequest($requestArr);
		
		$this->_language = array_shift($_rArr);	    // string
		$this->_controller = array_shift($_rArr);	// string
		$this->_display = array_shift($_rArr);		// string
		$this->_params = $_rArr;					// array
		
		// если язык не валиден, перегрузим страницу
		if (!IS_CLI && !in_array($this->_language, Lng::$allowedLngs)){
			App::redirectHref('');
		}
		
		// установить текущий язык
		if (!IS_CLI)
			Lng::setCurLanguage($this->_language);
	}
	
	// СПЕЦИАЛЬНОЕ ПРЕОБРАЗОВАНИЕ URL
	public function modifyRequest(&$requestArr){
		
		// индексы элементов
		$iController = 0;
		$iDisplay	 = 1;
		//$iParams	 = Все остальные элементы массива;
	}
	
	public function getArray(){
	
		return array($this->_controller, $this->_display, $this->_params);
	
	}
	
	public function getString(){
		
		return implode('/', $this->_requestArr);
	}
	
	/**
	 * GET APPENDED
	 * Получить массив Request дополненный одним или несколькими элементами.
	 * @param string|array $forAppend string|array - элемент(ы) для добавления
	 * @param bool $toArray - если TRUE - выводить как массив, иначе как строку
	 * @return string|array $_requestArr with appended string
	 */
	public function getAppended($forAppend, $toArray = FALSE){
		
		$output = array();
		foreach(array_merge($this->_requestArr, (array)$forAppend) as $item)
			if(strlen($item))
				$output[] = $item;
		
		return $toArray
			? $output
			: implode('/', $output);
	}
	
	public function getParts($indexes){

		$parts = array();
		foreach((array)$indexes as $i)
			if(isset($this->_requestArr[$i]))
				$parts[] = $this->_requestArr[$i];

		return implode('/', $parts);
	}
}

?>