<?php
class CallModule
{
	private $_appPar;
	private $_endpointPar;
	private $_localDb;
	private $_mongonDb;
	
	public function __construct()
	{
		$this->_appPar = Yii::app()->params['heal_config']['app_param'];
		$this->_endpointPar = Yii::app()->params['heal_config']['endpoint_param'];
	}
	
	private function getLocalDb()
	{
		if(!$this->_localDb){
			$this->_localDb = new MysqlManager(Yii::app()->db);
		}
		return $this->_localDb;
	}
	
	private function getMongoDb()
	{
		if(!$this->_mongonDb){
			$this->_mongonDb = new EMongoDocument();
		}
		return $this->_mongonDb;
	}
	
	
	public function call($data)
	{
		$appId = Yii::app()->params['call_config']['appId'];
		$token = Yii::app()->params['call_config']['token'];
		$url   = Yii::app()->params['call_config']['webcall'];
		$timestamp     		= time();
		$data['appId'] 		= $appId;
		$data['timestamp']  = $timestamp;
		$str = $appId.$token.$timestamp;
		$data['sign']  = md5($str);
		
		$ch = curl_init();			
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		if(is_array($data)){
			$sets = array();
			$hasfile = false;
			foreach ($data as $key => $val){
				$sets[] = $key . '=' . urlencode($val);
			}
			if(!$hasfile){
				$data = join('&',$sets);
			}
		}
			
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		
		// 设置超时参数(秒)
		curl_setopt($ch, CURLOPT_TIMEOUT, 20);
		
		ob_start();
		curl_exec($ch);
		$result = ob_get_contents();
		ob_end_clean();
		curl_close($ch);
		return $result;
		
	}
	
	public function saveSubmitData($data,$resultArr){
		$time = date('Y-m-d H:i:s');
		$sql = "INSERT INTO callout_log (ctrl_id,address,phone,warning_time,user_name,warn_id,submit_result,unique_id,clid,submit_description,insert_time,update_time,room_name,message)".
                "VALUE('%d','%s','%s','%s','%s','%d','%d','%s','%s','%s','%s','%s','%s','%s')";
        $sql = sprintf($sql,$data['ctrl_id'],$data['address'],$data['tel'],$data['warning_time'],$data['user_name'],$data['warn_id'],
        		$resultArr['result'],$resultArr['uniqueId'],$resultArr['clid'],$resultArr['description'],$time,$time,$data['room_name'],$data['message']); 
        $bResult = $this->getLocalDb()->ModelExecute($sql);
        if(!$bResult){
        	Yii::log('CallModule::saveSubmitData():insert ModelExecute() failed,sql is:'.$sql, 'info');
        	return false;
        }
        return true;      		
	}
	
	public function saveHangData($content){
	    if($content['answer_time'] == ""){
	    	$talk_second = 0;
	    	$talk_minute = 0;
	    	$answer_time = "0000-00-00 00:00:00";
	    }else{
	    	$talk_second = (int)$content['end_time']-(int)$content['answer_time'];
	    	$talk_minute = ceil($talk_second/60);
	    	$answer_time = date("Y-m-d H:i:s",$content['answer_time']);
	    }
		
		$sql = "UPDATE callout_log SET hang_start_time = '%s',hang_end_time = '%s',hang_answer_time = '%s',hang_status = '%d'"
				.",talk_time_minute = '%d',talk_time_second='%d',update_time = '%s' " 
                ." WHERE unique_id = '%s'";
		$sql = sprintf($sql,date("Y-m-d H:i:s",$content['start_time']),date("Y-m-d H:i:s",$content['end_time']),$answer_time,
				$content['status'],$talk_minute,$talk_second,date("Y-m-d H:i:s"),$content['uniqueId']);
//  		Yii::log('CallModule::saveHangData():update ModelExecute() failed,sql is:'.$sql, 'info');
		$bResult = $this->getLocalDb()->ModelExecute($sql);
		if(!$bResult){
			Yii::log('CallModule::saveHangData():update ModelExecute() failed,sql is:'.$sql, 'info');
			return false;
		}
		return true;
	}
}