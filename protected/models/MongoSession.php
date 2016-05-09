<?php
class MongoSession extends EMongoDocument {
	public $userId;
	public $_id;
	public $sessionId;
	public $activityType;
	public $startTime;
	public $duration;
	public $points;
	public $steps;
	public $calories;
	public $distance;
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
		$this->sessionId = $params['id'];
		$this->activityType = $params['activityType'];
		$this->startTime = $params['startTime'];
		$this->duration = $params['duration'];
		$this->points = $params['points'];
		$this->steps = $params['steps'];
		$this->calories = $params['calories'];
		$this->distance = $params['distance'];
	}

	public function getCollectionName()
	{
		return 'heal_session';
	}

	public function addInfo() {
		return $this->save();
	}
}
