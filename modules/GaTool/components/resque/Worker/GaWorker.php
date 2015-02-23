<?php

class GaWorker {
    
    private $table_name = '';
    private $service ;
    private $reporting_db;

    public function setUp() {
      $this->service  		= Yii::app()->getModule('GaTool')->JGoogleAPI->getService('Analytics');
      $this->reporting_db = Yii::app()->getModule('GaTool')->reporting_db;
    }

    public function perform() {
      date_default_timezone_set('Asia/Riyadh');
      $fields = $this->args;
      $this->table_name = $fields['table_name'];
      
      $profile = isset($fields['profile']) ? $fields['profile'] : 7729883;
      $from 	 = $fields['from'];
      $to 		 = $fields['to'];
      $metrices= $fields['metrices'];
      $filters = $fields['filters'];
      $dimensions = $fields['dimensions'];
      $segment = $fields['segments'];

      $segment_name  = null;
      $segment_value = null;
      
      if(!empty($segment)) {
        $parts = explode('::', $segment);
        $segment_name  = $parts[0];
        $segment_value = $parts[1];
      }

      $options = array(
          'max-results' => '1000',
          'start-index' => 1
      );

      if(!empty($segment)) {
      	$options ['segment'] = 'dynamic::' . $segment_value;
      }

      if(!empty($dimensions)) {
      	$options ['dimensions'] = $dimensions;
      }
      

      if(!empty($filters)) {
      	$options ['filters'] = $filters;
      }
      
			$data = $this->service->data_ga->get('ga:' . $profile,
        $from,
        $to, 
        $metrices,
        $options
      );

			$this->createTable($this->table_name, $data->columnHeaders, $segment_name);
			$this->insertRows($this->table_name, $data->rows, $segment_name);

			// Loop over other pages
			$next_index = 1;
			echo 'total results : ' . $data->totalResults.PHP_EOL;
			while($data->nextLink != null) {
				$next_index = $next_index + 500;
				echo 'fetching page with index : ' . $next_index .PHP_EOL;
				$options['start-index'] = $next_index;
				$data = $this->service->data_ga->get('ga:' . $profile,
					        $from,
					        $to, 
					        $metrices,
					        $options
					      );
				$this->insertRows($this->table_name, $data->rows, $segment_name);
				echo 'sleeping ....'.PHP_EOL;
				sleep(5);
			}

			echo 'done !! :) :* ' . PHP_EOL; 
    }

    public function tearDown() {

    }

    private function createTable($table_name, $coulmns, $segment = null) {
    		$mClms = array();
    		foreach ($coulmns as $c) {
    			if($c->dataType == "STRING") {
    				$mClms[] = str_replace('ga:', "ga_", $c->name) . ' VARCHAR(200)';
    			} elseif($c->dataType == "INTEGER") {
    				$mClms[] = str_replace('ga:', "ga_", $c->name) . ' INT';
    			} else {
            $mClms[] = str_replace('ga:', "ga_", $c->name) . ' VARCHAR(50)';            
          }

    		}
        
        if($segment != null) {
          $mClms[] = 'segment VARCHAR(100)';
        }

    		$sql   = 'CREATE TABLE IF NOT EXISTS ' . $table_name . ' ( ' . implode(',', $mClms) . ')';
    		print_r('============= creating table ============='.PHP_EOL);
    		print_r($sql);
    		$result = $this->reporting_db->createCommand($sql)->execute();
    		return true;
    }

    private function insertRows($table_name, $rows, $segment) {
    	if(!is_array($rows)) {
    		return;
    	}

    	foreach ($rows as $row) {
    		//process rows types 
    		$processd_clms = [];
    		foreach ($row as $clm) {
    			if(is_int($clm)) {
    				$processd_clms[] = $clm;
    			} else {
    				$processd_clms[] = '\'' . $clm . '\'';
    			}
    		}

        if($segment != null) {
          $processd_clms[] = '\'' . $segment . '\'';
        }

    		$insert_query = 'INSERT INTO ' . $table_name . ' VALUES( ' . implode(',', $processd_clms) . ')';
    		$result = $this->reporting_db->createCommand($insert_query)->execute();
    		print_r('insert row ....'.PHP_EOL);
    	}
    	return true;
    }

}
