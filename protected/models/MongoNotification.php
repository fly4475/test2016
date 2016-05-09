<?php
class MongoNotification extends EMongoDocument {
	public $Type;
	public $_id;
	public $MessageId;
	public $Message;
	public $Timestamp;
	public $SignatureVersion;
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
		$this->Type    = $params['Type'];
		$this->_id     = $params['MessageId'];
		$this->MessageId = $params['MessageId'];
		$this->Message = $params['Message'];
		$this->Timestamp = $params['Timestamp'];
		$this->SignatureVersion = $params['SignatureVersion'];
	}

	public function getCollectionName()
	{
		return 'heal_notification';
	}

	public function addInfo() {
		return $this->save();
	}
}
