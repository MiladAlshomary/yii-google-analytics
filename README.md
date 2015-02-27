# yii-google-analytics
Yii module provides a UI tool to create  [resque](https://github.com/resque/resque) jobs to retrieve data from google analytics into either Mysql or NoSql ( couchbase ) . With this tool You can create task with following options :
- Table name ( or bucket name )
- [Profile](https://developers.google.com/analytics/resources/concepts/gaConceptsAccounts) 
- Time portion ( from -> to )
- [Dimensions](https://developers.google.com/analytics/devguides/reporting/core/dimsmets) 
- Metrics
- [Segments](https://developers.google.com/analytics/devguides/reporting/core/v3/segments)
-  Filters 
- Where to save data ( currently there is mysql and [couchbase](http://www.couchbase.com/) options ).
Based on the the options you choose we create a table or bucket ( if you choose couchbase ) with appropriate number of columns and save your data in it .


----------
###Requirement :
- [Redis](http://redis.io/)
-  [Yii application](http://www.yiiframework.com/doc/guide/1.1/en/basics.application) to host the module. 
-  [php client for couchbase](http://docs.couchbase.com/couchbase-sdk-php-1.1/) ( if you want to take the couchbase option )
- mysql db with the following [schema](https://github.com/MiladAlshomary/yii-google-analytics/tree/master/schema) 

------
###Configurations :
In main.config and console.config add the following :

    'modules'=>array(
        'GaTool' => array(
            'components' => array(
                'JGoogleAPI' => array(
                    'class' => 'application.modules.GaTool.extensions.JGoogleAPI',
                    'defaultAuthenticationType'=>'serviceAPI',
                    'serviceAPI' => array(
                        'clientId' => '***********',
                        'clientEmail' => '*********',
                        'keyFilePath' => 'path/to/.p12',
                    )
                    'scopes' => array(
                        'serviceAPI' => array(
                            'Analytics' => array(                         'https://www.googleapis.com/auth/analytics'
                            )
                        ),
                    'useObjects'=>true
                ),
                'reporting_db' => array(
                    'class'=>'CDbConnection',
                    'connectionString' => 'mysql:host=yourhostname;dbname=yourdbname',
                    'username' => 'yourusername',
                    'password' => 'yourpassword',
                    'charset' => 'utf8',
                ),
            ),
            'profiles' => array(
                profile_id  => 'whatever the name'
            ),
            'redis' => array(
                'server' => 'redis server',
                'port' => 'redis port',
                'database' => 0,
                'password' => '',
            )
        )
 - in JGoogleAPI -> serviceAPI you add the following : clientId, clientEmail and keyFilePath . You get these information after you create a project on [google developer console](https://console.developers.google.com) . For farther information about the steps check [here](https://developers.google.com/analytics/solutions/articles/hello-analytics-api#introduction) 
 - in reporting_db you add the connectionString of your db ( server/dbname) , user and password .
 - In profiles you add list of profile id => profile name , which will be shown in the UI tool as a drop down list to pick what profile you want to query.
- In redis you add configuration of were is the redis you want [resque](https://github.com/resque/resque) to use for storing and consuming jobs.

----------------
## Running the workers :
Under GaTool -> components -> resque -> Worker you can find two classes : GaWorker and GaCbWorker , these two classes consume the jobs and retrieve the analytics.
To run the workers : you need to copy the [GaWorkerCommand](https://github.com/MiladAlshomary/yii-google-analytics/blob/master/commands/GaWorkerCommand.php) class into yii application commands .
And run the command under protected folder :

    $> php yiic gaworker start 1
This will run 1 instance of the workers , you can change 1 to the number of instances you want .
To stop the workers you type :

    $> php yiic gaworker stop

### Saving jobs Template :
you can save any task you create to be a template that you can use later.

### Job Status :
you can see the job you create in the side bar with status ( pending , running , completed, failed )

### Dealing with segments :
####How you add segments :
you can add multiple segments separated with space, each segment contains name and value separated with '::' , an example of a segment : 
`organic_sch::ga:medium=~organic,ga:source=~search|google.com;ga:source!~plus` 
where orangic_sch is the name of the segment and the rest is google syntax to defined the segment .


-------
##To DO :
- Adding predefined segments to the UI so users can use.
-  Instant updating of job status in the UI ( pending , done , failed ) 

