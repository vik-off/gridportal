<?php

class TaskSubmitController extends Controller{
	
	const DEFAULT_VIEW = 1;
	const TPL_PATH = 'TaskSubmit/';
	
	// методы, отображаемые по умолчанию
	protected $_defaultFrontendDisplay = 'list';
	protected $_defaultBackendDisplay = 'list';
	
	// права на выполнение методов контроллера
	public $permissions = array(
		'display_list' 			=> PERMS_UNREG,
		'display_view' 			=> PERMS_UNREG,
		'display_get_results'	=> PERMS_REG,
		'display_stop'			=> PERMS_REG,
		'display_delete'		=> PERMS_REG,
		'display_analyze'		=> PERMS_REG,
		
		'admin_display_list'	=> PERMS_ADMIN,
		'admin_display_new'		=> PERMS_ADMIN,
		'admin_display_edit'	=> PERMS_ADMIN,
		'admin_display_copy'	=> PERMS_ADMIN,
		'admin_display_delete'	=> PERMS_ADMIN,

		'action_save' 			=> PERMS_ADMIN,
		'action_stop' 			=> PERMS_REG,
		'action_delete' 		=> PERMS_REG,
		'action_get_results'	=> PERMS_REG,
	);
	
	protected $_title = null;
	
	/////////////////////
	////// DISPLAY //////
	/////////////////////
	
	/** DISPLAY LIST */
	public function display_list($params = array()){
		
		$collection = new TaskSubmitCollection();
		$variables = array(
			'collection' => $collection->getPaginated(),
			'pagination' => $collection->getPagination(),
			'sorters' => $collection->getSortableLinks(),
		);
		
		FrontendViewer::get()
			->setTitle('Коллекция')
			->setLinkTags($collection->getLinkTags())
			->setContentPhpFile(self::TPL_PATH.'list.php', $variables)
			->render();
	}
	
	/** DISPLAY VIEW */
	public function display_view($params = array()){
		
		$instanceId = getVar($params[0], 0, 'int');
		
			$variables = TaskSubmit::Load($instanceId)->GetAllFieldsPrepared();
			FrontendViewer::get()
				->setTitle('Детально')
				->setContentPhpFile(self::TPL_PATH.'view.php', $variables)
				->render();
	}
	
	/** DISPLAY GET-RESULTS */
	public function display_get_results($params = array()){
	
		$instanceId = getVar($params[0], 0 ,'int');
		$instance = Task::Load($instanceId);
		$user = CurUser::get();
		
		$manualMyproxyLogin = $user->getField('myproxy_manual_login') || $user->getField('myproxy_expire_date') < time();

		$variables = array_merge($instance->GetAllFieldsPrepared(), array(
			'instanceId' => $instanceId,
			'showMyproxyLogin' => $manualMyproxyLogin,
			'myproxyServersList' => $manualMyproxyLogin ? MyproxyServerCollection::load()->getAll() : array(),
		));
		
		FrontendViewer::get()
			->prependTitle('Сохранение результата задачи')
			->setContentPhpFile(self::TPL_PATH.'get_result.php', $variables)
			->render();
	}
	
	/** DISPLAY STOP */
	public function display_stop($params = array()){
	
		$instanceId = getVar($params[0], 0 ,'int');
		$instance = TaskSubmit::Load($instanceId);
		$user = CurUser::get();
		
		$manualMyproxyLogin = $user->getField('myproxy_manual_login') || $user->getField('myproxy_expire_date') < time();

		$variables = array_merge($instance->GetAllFieldsPrepared(), array(
			'instanceId' => $instanceId,
			'showMyproxyLogin' => $manualMyproxyLogin,
			'myproxyServersList' => $manualMyproxyLogin ? MyproxyServerCollection::load()->getAll() : array(),
		));
		
		FrontendViewer::get()
			->prependTitle('Остановка задачи')
			->setContentPhpFile(self::TPL_PATH.'stop.php', $variables)
			->render();
	}
	
	/** DISPLAY DELETE */
	public function display_delete($params = array()){
		
		$instanceId = getVar($params[0], 0 ,'int');
		$instance = TaskSubmit::Load($instanceId);

		$variables = array_merge($instance->GetAllFieldsPrepared(), array(
			'instanceId' => $instanceId,
		));
		
		FrontendViewer::get()
			->prependTitle('Удаление задачи')
			->setContentPhpFile(self::TPL_PATH.'delete.php', $variables)
			->render();
		
	}
	
	
	///////////////////////////
	////// DISPLAY ADMIN //////
	///////////////////////////
	
	/** DISPLAY LIST (ADMIN) */
	public function admin_display_list($params = array()){
		
		$collection = new TaskSubmitCollection();
		$variables = array(
			'collection' => $collection->getPaginated(),
			'pagination' => $collection->getPagination(),
			'sorters' => $collection->getSortableLinks(),
		);
		
		BackendViewer::get()
			->prependTitle('Список элементов')
			->setLinkTags($collection->getLinkTags())
			->setContentPhpFile(self::TPL_PATH.'admin_list.php', $variables);
	}
	
	/** DISPLAY NEW (ADMIN) */
	public function admin_display_new($params = array()){
		
		$pageTitle = 'Создание новой страницы';
		
		$variables = array_merge($_POST, array(
			'instanceId' => 0,
			'pageTitle'  => $pageTitle,
			'validation' => TaskSubmit::Create()->getValidator()->getJsRules(),
		));
		
		BackendViewer::get()
			->prependTitle($pageTitle)
			->setBreadcrumbs('add', array(null, $pageTitle))
			->setContentPhpFile(self::TPL_PATH.'edit.php', $variables);
	}
	
	/** DISPLAY EDIT (ADMIN) */
	public function admin_display_edit($params = array()){
		
		$instanceId = getVar($params[0], 0 ,'int');
		$instance = TaskSubmit::Load($instanceId);
		
		$pageTitle = '<span style="font-size: 14px;">Редактирование элемента</span> #'.$instance->getField('id');
	
		$variables = array_merge($instance->GetAllFieldsPrepared(), array(
			'instanceId' => $instanceId,
			'pageTitle'  => $pageTitle,
			'validation' => $instance->getValidator()->getJsRules(),
		));
		
		BackendViewer::get()
			->prependTitle('Редактирование записи')
			->setBreadcrumbs('add', array(null, 'Редактирование записи'))
			->setContentPhpFile(self::TPL_PATH.'edit.php', $variables);
		
	}
	
	/** DISPLAY COPY (ADMIN) */
	public function admin_display_copy($params = array()){
		
		$instanceId = getVar($params[0], 0 ,'int');
		$instance = TaskSubmit::Load($instanceId);
		
		$pageTitle = 'Копирование записи';
	
		$variables = array_merge($instance->GetAllFieldsPrepared(), array(
			'instanceId' => 0,
			'pageTitle'  => $pageTitle,
			'validation' => $instance->getValidator()->getJsRules(),
		));
		
		BackendViewer::get()
			->prependTitle($pageTitle)
			->setBreadcrumbs('add', array(null, $pageTitle))
			->setContentPhpFile(self::TPL_PATH.'edit.php', $variables);
		
	}
	
	/** DISPLAY DELETE (ADMIN) */
	public function admin_display_delete($params = array()){
		
		$instanceId = getVar($params[0], 0 ,'int');
		$instance = TaskSubmit::Load($instanceId);

		$variables = array_merge($instance->GetAllFieldsPrepared(), array(
			'instanceId' => $instanceId,
		));
		
		BackendViewer::get()
			->prependTitle('Удаление записи')
			->setBreadcrumbs('add', array(null, 'Удаление записи'))
			->setContentPhpFile(self::TPL_PATH.'delete.php', $variables);
		
	}
	

	////////////////////
	////// ACTION //////
	////////////////////
	
	/** ACTION SAVE (ADMIN) */
	public function action_save($params = array()){
		
		$instanceId = getVar($_POST['id'], 0, 'int');
		$instance = new TaskSubmit($instanceId);
		
		if($instance->save($_POST)){
			Messenger::get()->addSuccess('Запись сохранена');
			$this->_redirectUrl = !empty($this->_redirectUrl) ? preg_replace('/\(%([\w\-]+)%\)/e', '$instance->getField("$1")', $this->_redirectUrl) : null;
			return TRUE;
		}else{
			Messenger::get()->addError('Не удалось сохранить запись:', $instance->getError());
			return FALSE;
		}
	}
	
	/** ACTION STOP */
	public function action_get_results($params = array()){
		
		$instanceId = getVar($_POST['id'], 0, 'int');
		$instance = TaskSubmit::load($instanceId);
		$instance->setSetInstance( TaskSet::load($instance->getField('set_id')) );
		
		$myProxyAuthData = !empty($_POST['myproxy-autologin'])
			? CurUser::get()->getMyproxyLoginData()
			: array(
				'serverId' => getVar($_POST['server']),
				'login' => getVar($_POST['user']['name']),
				'password' => getVar($_POST['user']['pass']),
				'lifetime' => getVar($_POST['lifetime']),
			);
	
		if($instance->getResults($myProxyAuthData)){
			Messenger::get()->addSuccess(Lng::get('xrls_edit.success'));
			return TRUE;
		}else{
			Messenger::get()->addError('Не удалось получить задачу:', $instance->getError());
			return FALSE;
		}
	}
	
	/** ACTION STOP */
	public function action_stop($params = array()){
		
		$instanceId = getVar($_POST['id'], 0, 'int');
		$instance = TaskSubmit::load($instanceId);
		$instance->setSetInstance( TaskSet::load($instance->getField('set_id')) );
		
		$myProxyAuthData = !empty($_POST['myproxy-autologin'])
			? CurUser::get()->getMyproxyLoginData()
			: array(
				'serverId' => getVar($_POST['server']),
				'login' => getVar($_POST['user']['name']),
				'password' => getVar($_POST['user']['pass']),
				'lifetime' => getVar($_POST['lifetime']),
			);
	
		if($instance->stop($myProxyAuthData)){
			Messenger::get()->addSuccess('Задача остановлена и удалена.');
			return TRUE;
		}else{
			Messenger::get()->addError('Не удалось остановить задачу:', $instance->getError());
			return FALSE;
		}
	}
	
	/** ACTION DELETE */
	public function action_delete($params = array()){
		
		$instanceId = getVar($_POST['id'], 0, 'int');
		$instance = TaskSubmit::load($instanceId);
		$instance->setSetInstance( TaskSet::load($instance->getField('set_id')) );
	
		if($instance->destroy()){
			Messenger::get()->addSuccess(Lng::get('task.controller.RecordRemoveSucsess'));
			return TRUE;
		}else{
			Messenger::get()->addError('Не удалось удалить запись:', $instance->getError());
			return FALSE;
		}

	}
	
}

?>