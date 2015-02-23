<?php

class GaCbWorker {
    
    private $service ;
    private $cb_cluster;

    public function setUp() {
      $this->service  		= Yii::app()->getModule('GaTool')->JGoogleAPI->getService('Analytics');
      $couchbase = Yii::app()->getModule('GaTool')->params['couchbase'];
      
      $server = isset($couchbase['server']) ? $couchbase['server'] : 'localhost';
      $port   = isset($couchbase['port']) ? ':' . $couchbase['port'] : '';

      $this->cb_cluster = new CouchbaseCluster('couchbase://' . $server . $port);

    }

    public function perform() {
      date_default_timezone_set('Asia/Riyadh');
      $fields = $this->args;
      
      $bucket_name = $fields['table_name'];
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

			$this->pushDocuments($bucket_name, $data->rows, $data->columnHeaders, $segment_name);

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
				$this->pushDocuments($bucket_name, $data->rows, $data->columnHeaders, $segment_name);
				echo 'sleeping ....'.PHP_EOL;
				sleep(5);
			}

			echo 'done !! :) :* ' . PHP_EOL; 
    }

    public function tearDown() {

    }

    private function pushDocuments($bucket_name, $rows, $columns, $segment) {
    	if(!is_array($rows)) {
    		return;
    	}

      $bucket = $this->cb_cluster->openBucket($bucket_name);
    	foreach ($rows as $row) {
        $document = [];
        $idx = 0 ;
        foreach ($columns as $column) {
          $document[$column->name] = $row[$idx];
          $idx ++;
        }
        if($segment != null) {
          $document['segment'] = $segment;
        }
        echo 'push document ...'.PHP_EOL;
        $bucket->insert((string) time() , $document);
        sleep(1);
    	}

    	return true;
    }

}
