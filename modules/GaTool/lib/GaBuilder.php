<?php

class GaBuilder {

	public static function createJob($table_name, $from, $to, $metrices, $filters, $dimensions, $segments, $profile, $output) {
    
		switch ($output) {
			case 'mysql':
				$token = Resque::enqueue('ga_jobs', 'GaWorker', 
    								['table_name' => $table_name,'profile' => $profile,'from' => $from, 'to' => $to, 'metrices' => $metrices, 'filters' => $filters, 'dimensions' => $dimensions, 'segments' => $segments]
    								, true);
				break;
				
			case 'couchbase':
				$token = Resque::enqueue('ga_jobs', 'GaCbWorker', 
    								['table_name' => $table_name,'profile' => $profile,'from' => $from, 'to' => $to, 'metrices' => $metrices, 'filters' => $filters, 'dimensions' => $dimensions, 'segments' => $segments]
    								, true);
				break;
				default:
				$token = Resque::enqueue('ga_jobs', 'GaWorker', 
    								['table_name' => $table_name,'profile' => $profile,'from' => $from, 'to' => $to, 'metrices' => $metrices, 'filters' => $filters, 'dimensions' => $dimensions, 'segments' => $segments]
    								, true);
				break;
		}

		if(isset($token)) {
    	self::saveJob($token);
  	}

	}

	private function saveJob($token) {
		try{
			$db = Yii::app()->getModule('GaTool')->reporting_db;
			$result = $db->createCommand('INSERT INTO jobs (token) VALUES(\'' . $token . '\')')->execute();
		} catch(Exception $ex) {
			print_r($ex->getMessage());
		}
	}
}

?>