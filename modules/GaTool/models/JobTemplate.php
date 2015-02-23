<?php

class JobTemplate extends CActiveRecord {
 
    private static $reporting_db;
    
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    public function getDbConnection() {
        if (self::$reporting_db !== null){
            return self::$reporting_db;
        } else {
            self::$reporting_db = Yii::app()->getModule('GaTool')->reporting_db;
            if (self::$reporting_db instanceof CDbConnection) {
                self::$reporting_db->setActive(true);
                return self::$reporting_db;
            } else {
                throw new CDbException(Yii::t('yii','Active Record requires a "db" CDbConnection application component.'));
            }
        }
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'job_templates';
    }
}