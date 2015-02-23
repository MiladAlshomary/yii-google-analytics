<?php

class TaskController extends Controller {

	private $jStatuses = [1 => 'WAITING', 2 => 'RUNNING', 3=> 'FAILED', 4=> 'COMPLETE'];
	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
			'postOnly + delete', // we only allow deletion via POST request
		);
	}

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules(){
		return array(
			array('allow',
				'actions'=>array('index','create','jobtemplate'),
				'roles' => array('Admin', 'Analyst'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex() {
		$job_templates = JobTemplate::model()->findAll();
		$profiles  = Yii::app()->getModule('GaTool')->profiles;
		$tasks_status = $this->getTasksStatus();

		$this->render('index', array('profiles' => $profiles, 'job_templates' => $job_templates, 'tasks_status' => $tasks_status));
	}

	public function actionCreate() {

		$table_name = isset($_POST['table_name']) ? $_POST['table_name'] : '';
		$profile 		= isset($_POST['profile']) ? $_POST['profile'] : '';
		$metrcis 		= isset($_POST['metrcis']) ? $_POST['metrcis'] : '';
		$dimensions = isset($_POST['dimensions']) ? $_POST['dimensions'] : '';
		$filters 		= isset($_POST['filters']) ? $_POST['filters'] : '';
		$segments 	= isset($_POST['segments']) ? trim($_POST['segments']) : '';
		$from 			= isset($_POST['from']) ? $_POST['from'] : '';
		$to 				= isset($_POST['to']) ? $_POST['to'] : '';
		$output			= isset($_POST['output_to']) ? $_POST['output_to'] : 'mysql';

		if(isset($_POST['save_as_template'])) {
			$name = isset($_POST['template_name']) ? $_POST['template_name'] : 'default_template';
			$this->saveTemplate($name, $table_name, $profile, $metrcis, $dimensions, $filters, $segments, $from, $to);
		}

		$job_templates = JobTemplate::model()->findAll();
		$tasks_status = $this->getTasksStatus();
		if(empty($table_name) || empty($metrcis) || empty($from) || empty($to) || empty($profile)) {
			return $this->render('index', ['msg' => 'Fille all required fields', 'job_created' => false, 'profiles' => Yii::app()->getModule('GaTool')->profiles,  'job_templates' => $job_templates, 'tasks_status' => $tasks_status]);
		} else {

			if(empty($segments)) {
				
				GaBuilder::createJob($table_name,
					$from, 
					$to, 
					$metrcis, 
					$filters, 
					$dimensions,
					$segments,
					$profile,
					$output
				);

			} else {
				
				$segments_array = explode(' ', $segments);
				foreach ($segments_array as $segment) {
					GaBuilder::createJob($table_name,
						$from, 
						$to, 
						$metrcis, 
						$filters, 
						$dimensions,
						$segment,
						$profile,
						$output
					);	
				}
			
			}

			$tasks_status = $this->getTasksStatus();
			return $this->render('index', ['msg' => 'job created !!', 'job_created' => true, 'profiles' => Yii::app()->getModule('GaTool')->profiles,  'job_templates' => $job_templates, 'tasks_status' => $tasks_status]);
		}
	
	}

	public function actionJobtemplate($id) {
		$template = JobTemplate::model()->findByPk($id);
		$job_templates = JobTemplate::model()->findAll();
		$profiles  = Yii::app()->getModule('GaTool')->profiles;
		$tasks_status = $this->getTasksStatus();
		$this->render('index', array('profiles' => $profiles, 'job_templates' => $job_templates, 'template' => $template, 'tasks_status' => $tasks_status));	
	}

	private function getTasksStatus() {
		$db = Yii::app()->getModule('GaTool')->reporting_db;
		$tasks  = [];
		$result = $db->createCommand('SELECT * FROM jobs order by id Desc limit 5')->query();
		foreach ($result as $row) {
			$rStatus = new Resque_Job_Status($row['token']);
			$status  = $rStatus->get();
			if($status == false) {
				continue;
			}
			$tasks[$row['token']] =  $this->jStatuses[$status];
		}
		return $tasks;
	}

	private function saveTemplate($name, $table_name, $profile, $metrcis, $dimensions, $filters, $segments, $from, $to) {
		$model = new JobTemplate();
		$model->name = $name;
		$model->table_name = $table_name;
		$model->profile = $profile;
		$model->metrcis = $metrcis;
		$model->dimensions = $dimensions;
		$model->filters = $filters;
		$model->segments = $segments;
		$model->date_from = $from;
		$model->date_to = $to;

		$model->save();
	
	}

}