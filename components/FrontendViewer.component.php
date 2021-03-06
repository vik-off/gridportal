<?

class FrontendViewer extends CommonViewer{
	
	protected $_skin = 'frontend';
		
	private $_topMenuItems = array(
		'main' =>                  array('href' => '', 'title' => 'top-menu.main'),
		'projects' =>              array('href' => 'project/list', 'title' => 'top-menu.projects'),
		'tasks' =>                 array('href' => 'task-set', 'title' => 'top-menu.tasks'),
		'analyze' =>               array('href' => 'task/analyze', 'title' => 'top-menu.analyze'),
		'grid-certificates' =>     array('href' => 'page/grid-certificates', 'title' => 'top-menu.grid-certificates'),
		'virtual-organizations' => array('href' => 'page/virtual-organizations', 'title' => 'top-menu.virtual-organizations'),
	);

	// активный пункт главного меню
	private $_topMenuActiveItem = null;
	
	private static $_instance = null;
	
	
	/** ТОЧКА ВХОДА В КЛАСС (ПОЛУЧИТЬ ЭКЗЕМПЛЯР CommonViewer) */
	public static function get(){
		
		if(is_null(self::$_instance))
			self::$_instance = new FrontendViewer();
		
		return self::$_instance;
	}
	
	/** ИНИЦИАЛИЗАЦИЯ */
	protected function init(){

		$this->_topMenu = new Menu('frontend-top');
	}
	
	public function getLoginBlock(){
		
		if (CurUser::get()->isLogged()) {
			
			$user_io = CurUser::get()->getName('if');
			$user_perms_string = User::getPermName(USER_AUTH_PERMS);
			include($this->_tplPath.'Profile/logged_block.php');
		} else {
			$vars = array('error' => Messenger::get('login')->getAll());
			return $this->getContentPhpFile('Profile/login_block.php', $vars);
		}
	}
	
	public function getLanguageBlock(){
	
		$langs = Lng::$allowedLngs;
		$curLng = Lng::get()->getCurLng();
		include($this->_tplPath.'language_block.php');
	}
	
	public function setTopMenuActiveItem($active){
		
		$this->_topMenuActiveItem = $active;
		return $this;
	}
	
	protected function _getTopMenuHTML(){

		/*$html = '';
		foreach($this->_topMenu->getItems() as $item)
			$html .= '<a href="'.$item['href'].'" '.(!empty($item['attrs']) ? $item['attrs'] : '').' '.($item['active'] ? 'class="active"' : '').'>'.Lng::get($item['title']).'</a> ';*/
		$html = '<ul>';
		foreach($this->_topMenu->getItems() as $item)
			$html .= '<li><a href="'.$item['href'].'" '.(!empty($item['attrs']) ? $item['attrs'] : '').' '.($item['active'] ? 'class="active"' : '').'>'.Lng::get($item['title']).'</a></li>';
		$html .= '</ul>';

		return $html;
	}

	public function error403($message = ''){
		
		// для неавторизованных пользователей вывести форму авторизации
		if(USER_AUTH_PERMS == PERMS_UNREG){
			
			$errorMessage = Messenger::get()->ns('login')->getAll();
			include($this->_tplPath.'login.php');
		}
		// для авторизованных пользователей показать страницу 403
		else{
			header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden'); // 'HTTP/1.1 403 Forbidden'
			
			$variables = array(
				'message' => User::hasPerm(Error::getConfig('minPermsForDisplay')) ? $message : '',
			);
			$this
				->setTitle('Доступ запрещен')
				->setContentPhpFile('403.php', $variables)
				->render();
		}
		
		exit();
	}
	
	public function userBlocked(){
		
		$variables = array();
		
		echo $this->getContentPhpFile('user_blocked.php');
		exit;
	}
}

?>