<?php

class GaWorkerCommand extends CConsoleCommand {
    
    public $defaultAction = 'index';
    
    public function actionStart($interval = 5, $verbose = 1, $count = 5){
        
        $return = null;
        $yiiPath = Yii::getPathOfAlias('system');
        $appPath = Yii::getPathOfAlias('application');
        $resquePath = Yii::getPathOfAlias('application.modules.GaTool.components.resque');
        
        $server = 'localhost';
        $port = 6379;
        $host = $server.':'.$port;
        $db =  0;
        $auth = '';
        $prefix = '';

        $includeFiles = '';
        if (is_array($includeFiles)) {
            $includeFiles = implode(',', $includeFiles);
        }

        $command = 'nohup sh -c "PREFIX='.$prefix.' QUEUE=ga_jobs COUNT='.$count.' REDIS_BACKEND='.$host.' REDIS_BACKEND_DB='.$db.' REDIS_AUTH='.$auth.' INTERVAL='.$interval.' VERBOSE='.$verbose.' INCLUDE_FILES='.$includeFiles.' YII_PATH='.$yiiPath.' APP_PATH='.$appPath.' php '.$resquePath.'/bin/resque" >> '.$appPath.'/runtime/yii_ga_tool_log.log 2>&1 &';

        exec($command, $return);

        return $return;
    }

    public function actionStop($quit = null) {
        $quit_string = $quit ? '-s QUIT': '-9';
        exec("ps uxe | grep '".escapeshellarg(Yii::app()->basePath)."' | grep 'resque' | grep -v grep | awk {'print $2'} | xargs kill $quit_string");
    }
}
