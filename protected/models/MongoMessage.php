<?php
class MongoMessage extends EMongoDocument {
	public $type;
	public $_id;
	public $msgId;
	public $ownerId;
	public $action;
	public $updatedAt;
	public $insert_time;
	
		
	public static function model($className = __CLASS__){
		return parent::model($className);
	}
	
	public function __construct()
	{
		$this->insert_time = date('Y-m-d H:i:s');
	}
	
	public function setParam($params)
	{
		$this->type    	= $params['type'];
		$this->_id 		= $params['id'];
		$this->msgId 	= $params['id'];
		$this->ownerId 	= $params['ownerId'];
		$this->action = $params['action'];
		$this->updatedAt = $params['updatedAt'];
	}

	public function getCollectionName()
	{
		return 'heal_message';
	}

	public function addInfo() {
		return $this->save();
	}
}
