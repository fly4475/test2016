<?php
class MongoSleep extends EMongoDocument {
	public $userId;
	public $_id;
	public $sleepId;
	public $autoDetected;
	public $startTime;
	public $duration;
	public $sleepDetails;
	public $insert_time;
	
		
	public static function model($className = __CLASS__){
		return parent::model($className);
	}
	
	public function __construct()
	{
		$this->insert_time = date('Y-m-d H:i:s');
	}
	
	public function setParam($params,$userId)
	{
		$this->userId    = $userId;
		$this->_id = $params['id'];
		$this->sleepId = $params['id'];
		$this->autoDetected = $params['autoDetected'];
		$this->startTime = $params['startTime'];
		$this->duration = $params['duration'];
		$this->sleepDetails = $params['sleepDetails'];
	}

	public function getCollectionName()
	{
		return 'heal_sleep';
	}

	public function addInfo() {
		return $this->save();
	}
}
