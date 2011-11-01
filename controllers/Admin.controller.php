<?

//  admin/content/page/edit/1

class AdminController extends Controller{
	
	const DEFAULT_VIEW = 1;
	const TPL_PATH = 'Admin/';
	
	// методы, отображаемые по умолчанию
	protected $_defaultFrontendDisplay = FALSE;
	protected $_defaultBackendDisplay = 'content';
	
	// права на выполнение методов контроллера
	public $permissions = array(
	
		'display_content'	=> PERMS_ADMIN,
		'display_users' 	=> PERMS_ADMIN,
		'display_root' 		=> PERMS_ADMIN,
		'display_sql'       => PERMS_ADMIN,
		
		'action_sql_dump' 	=> PERMS_ADMIN,
		'action_sql_load_dump' 	=> PERMS_ADMIN,
		
		'ajax_get_tables_by_db' => PERMS_ADMIN,
	);
	
	public function init(){
	
		$this->_adminMode = TRUE;
		BackendViewer::get()->setTitle('Административная панель');
	}
	
	// МОДИФИКАЦИЯ ИМЕНИ МЕТОДА
	public function modifyMethodName(&$method){
		// для этого контроллера модификация имен не требуется
	}
	
	
	/////////////////////
	////// DISPLAY //////
	/////////////////////
	
	// DISPLAY CONTENT
	public function display_content($params = array()){
		
		$viewer = BackendViewer::get();
		$viewer
			->setTopMenuActiveItem('content')
			->setLeftMenuType('content');

		if(empty($params[0])){
			$viewer
				->setContentHtmlFile(self::TPL_PATH.'content_index.tpl')
				->setBreadcrumbs('auto')
				->render();
			exit();
		}
		
		$controllerIdentifier = array_shift($params);
		$controllerClass = App::getControllerClassName($controllerIdentifier);
		$displayMethodIdentifier = array_shift($params);
		
		if(!$controllerClass){
			BackendViewer::get()->error404('Контроллер не найден');
			exit();
		}
		
		$controllerInstance = new $controllerClass($adminMode = TRUE);
		$controllerInstance->performDisplay($displayMethodIdentifier, $params);
		$viewer
			->setLeftMenuActiveItem($controllerIdentifier)
			->setBreadcrumbs('auto')
			->render();
	}

	// DISPLAY USERS
	public function display_users($params = array()){
			
		$viewer = BackendViewer::get();
		$viewer
			->setTopMenuActiveItem('users')
			->setLeftMenuType('users');
			
		
		if(empty($params[0])){
			$viewer
				->setContentHtmlFile(self::TPL_PATH.'users_index.tpl')
				->setBreadcrumbs('auto')
				->render();
			exit();
		}
		
		$controllerInstance = new UserController($adminMode = TRUE);
		$displayMethodIdentifier = array_shift($params);
		
		$controllerInstance->performDisplay($displayMethodIdentifier, $params);
		
		$viewer
			->setLeftMenuActiveItem($displayMethodIdentifier)
			->setBreadcrumbs('auto')
			->render();
	}
	
	// DISPLAY ROOT
	public function display_root($params = array()){
		
		$section = getVar($params[0]);
		
		$viewer = BackendViewer::get();
		$viewer
			->setTopMenuActiveItem('root')
			->setLeftMenuType('root')
			->setLeftMenuActiveItem($section)
			->setBreadcrumbs('auto');

		if(!$section){
			$viewer
				->setContentHtmlFile(self::TPL_PATH.'root_index.tpl')
				->render();
			exit();
		}
		
		switch($section){
			
			case 'sql':
				
				$this->snippet_sql_console();
				break;
			
			case 'error-log':
				$this->snippet_error_log();
				break;
				
			default:
				$controllerClass = App::getControllerClassName(array_shift($params));
				$displayMethodIdentifier = array_shift($params);
				
				if(!$controllerClass){
					BackendViewer::get()->error404('Контроллер не найден');
					exit();
				}
				
				$controllerInstance = new $controllerClass($adminMode = TRUE);
				$controllerInstance->performDisplay($displayMethodIdentifier, $params);
		}
		
		$viewer->render();
	}

	// DISPLAY SQL
	public function display_sql($params = array()){
		
		$section = getVar($params[0]);
		
		$viewer = BackendViewer::get();
		$viewer
			->setTopMenuActiveItem('sql')
			->setLeftMenuType('sql')
			->setLeftMenuActiveItem($section)
			->setBreadcrumbs('auto');

		if(!$section){
			$viewer
				->setContentHtmlFile(self::TPL_PATH.'sql_index.tpl')
				->render();
			exit();
		}
		
		switch($section){
			
			case 'console':
				
				$this->snippet_sql_console();
				break;
			
			case 'make-dump':
				
				$this->snippet_sql_dump();
				break;
			
			case 'load-dump':
				$this->snippet_sql_load_dump();
				break;
		}
		
		$viewer->render();
	}
	
	//////////////////////
	////// SNIPPETS //////
	//////////////////////
	
	// SNIPPET SQL CONSOLE
	public function snippet_sql_console(){
		
		$variables = array();
		$query = stripslashes(getVar($_POST['query']));
		$variables['query'] = $query;
		
		if($query){
		
			$variables['data'] = $this->_execSql($query);
			$variables['sql_error'] = db::get()->hasError() ? db::get()->getError() : '';
		}
		BackendViewer::get()
			->setContentPhpFile(self::TPL_PATH.'sql_console.php', $variables);
	}
	
	// SNIPPET SQL DUMP
	public function snippet_sql_dump(){
		
		$db = db::get();
		
		$variables = array(
			'databases' => $db->showDatabases(),
			'curDatabase' => $db->getDatabase(),
			'encoding' => $db->getEncoding(),
		);

		BackendViewer::get()
			->setContentPhpFile(self::TPL_PATH.'sql_dump.php', $variables);
	}
	
	// SNIPPET SQL LOAD DUMP
	public function snippet_sql_load_dump(){

		BackendViewer::get()
			->setContentPhpFile(self::TPL_PATH.'sql_load_dump.php');
	}
	
	// SNIPPET ERROR LOG
	public function snippet_error_log(){
		
		$collection = new ErrorCollection();
		$variables = array(
			'collection' => $collection->getPaginated(),
			'pagination' => $collection->getPagination(),
		);
		BackendViewer::get()
			->setContentPhpFile(self::TPL_PATH.'root_error_log.php', $variables);
	}
	
	////////////////////
	////// ACTION //////
	////////////////////
	
	// ACTION SQL DUMP
	public function action_sql_dump(){
		
		$tblInputType = $_POST['tables-input-type'];
		$database = getVar($_POST['database'], null);
		$encoding = getVar($_POST['encoding'], null);
		$tables = null;
		$db = db::get();
		
		if($tblInputType == 'text'){
			$tables = explode(',', getVar($_POST['tables-text']));
			foreach($tables as &$tbl)
				$tbl = trim($tbl);
		}
		elseif($tblInputType == 'select'){
			$tables = getVar($_POST['tables-select']);
		}
		
		// установка корировки соединения (если задана)
		if(!empty($encoding))
			$db->setEncoding($encoding);
		
		$db->makeDump($database, $tables);
		exit;
	}
	
	// ACTION SQL LOAD DUMP
	public function action_sql_load_dump($params = array()){
		
		if(!empty($_FILES['dump']) && file_exists($_FILES['dump']['tmp_name'])){
			
			$db = db::get();
			
			if($db->loadDump($_FILES['dump']['tmp_name'])){
				Messenger::get()->addSuccess('Дамп успешно загружен');
				return TRUE;
			}else{
				Messenger::get()->addError('Не удалось загрузить дамп', $db->getError());
				return FALSE;
			}
		}
		else{
			return FALSE;
		}
	}
	
	// DELETE OLD ERRORS
	public function action_delete_old_errors($params = array()){
		
		$expire = getVar($_POST['expire']);
		
		$expiredValues = array(
			'1day'   => 86400,
			'1week'  => 604800,
			'1month' => 2592000,
			'3month' => 7776000,
			'6month' => 15552000,
			'9month' => 23328000,
			'1year'  => 31536000);
			
		if(!isset($expiredValues[$expire])){
			Messenger::get()->addError('Неверный временной промежуток.');
			return FALSE;
		}
		
		UserStatistics::get()->deleteOldStatistics($expiredValues[$expire]);
		Messenger::get()->addSuccess('Старая статистика удалена.');
		return TRUE;
	}
	
	
	////////////////////
	////// AJAX   //////
	////////////////////
	
	// AJAX GET TABLES BY DB
	public function ajax_get_tables_by_db($params = array()){
		
		$dbName = getVar($_POST['db']);
		if(empty($dbName))
			return '';
		
		$db = db::get();
		$db->selectDb($dbName);
		echo json_encode($db->showTables());
	}
	
	////////////////////
	////// OTHER  //////
	////////////////////
	
	// EXEC SQL (FORM SQL CONSOLE)
	private function _execSql($sqls){
		
		$db = db::get();
		$sqls = preg_replace('/;\r\n/', ";\n", $sqls);
		$sqlsArr = explode(";\n", $sqls);
		$results = array();
		
		$db->enableErrorHandlingMode();
		
		foreach($sqlsArr as $sql){
			$sql = trim($sql);
			if(!empty($sql))
				$results[] = $db->getAll($sql, array());
		}
		
		$db->disableErrorHandlingMode();
		
		return $results;
	}
	
	// ОБРАБОТЧИК 403
	public function error403handler($method, $line = 0){
		
		BackendViewer::get()->showLoginPage();
	}

}

?>