<?php
class HealModule
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
			$this->_localDb = new MysqlManager(Yii::app()->db_heal);
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
	
	public function test()
	{
		$this->_mongonDb->save();
	}
	
	public function authorize($param)
	{
		$body = array(
				'client_id'=>$this->_appPar['clientKey'],
				'response_type'=>$this->_appPar['response_type'],
				'redirect_uri'=>$this->_appPar['redirect_uri'].'?family_id='.$param,
				'scope'=>$this->_appPar['scope'],
		);
		$prestr = Fnlib::createLinkstring($body);
		$url = sprintf('%s%s?%s',$this->_appPar['apiRoot'],$this->_endpointPar['authorize'],$prestr);
		return $url;
	}
	
	
	public function execute($method, $url, $fields='', $opts=array(),$resource=''){
		try{
			$ch = curl_init();
			
			curl_setopt($ch, CURLOPT_URL, $url);
			
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
			
			
			// 根据不同的方法处理参数
			$method = strtolower($method);
			if('post' == $method){
				curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
				curl_setopt($ch, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
					
				curl_setopt($ch, CURLOPT_POST, 1);
				if(is_array($fields)){
					$sets = array();
					$hasfile = false;
					foreach ($fields as $key => $val){
						$sets[] = $key . '=' . urlencode($val);
					}
					if(!$hasfile){
						$fields = join('&',$sets);
					}
				}
					
				curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
			}
			
			// 设置超时参数(秒)
			curl_setopt($ch, CURLOPT_TIMEOUT, 20);
			
			// 设置用户头信息
			$headers = $opts['headers'];
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
				
			
			ob_start();
			curl_exec($ch);
			$result = ob_get_contents();
			ob_end_clean();
			
			// 检查是否有错误发生
			if(!$result){
				$info = curl_getinfo($ch);
				Yii::log('HealModule::execute() '.$resource.' post or get is fail,data is:'.json_encode($info), 'log');
				die($resource." execute error,curl_error is:".curl_error($ch));
			}
			
			curl_close($ch);
			return $result;
			
		}catch(Exception $e){
			Yii::log('execute Exception:'.$e,'info');
			return false;
		}		
	}
	
	
	public function exchange($code,$family_id)
	{
		$url = $this->_appPar['apiRoot'].$this->_endpointPar['exchange'];
		$body = array(
			'grant_type'=>'authorization_code',
			'code'=>$code,
			'client_id'=>$this->_appPar['clientKey'],
			'client_secret'=>$this->_appPar['clientSecret'],
			'redirect_uri'=>$this->_appPar['redirect_uri'].'?family_id='.$family_id,//should be the same with the uri in the authorize step
		);
		$opts['headers']  = array(
				'Content-Type'=>'application/x-www-form-urlencoded'
		);
 		$response = $this->execute('post',$url,$body,$opts,'exchange');
		return $response;
		
	}
	
	public function getResource($resource, $params)
	{
		$url = $this->_appPar['apiRoot'].$this->_endpointPar[$resource];
		if(isset($params['id'])){
			$url = $this->_appPar['apiRoot'].$this->_endpointPar[$resource.'WithId'];
			$url = str_replace(':id',$params['id'],$url);
			unset($params['id']);
		}
		if(isset($params['userId'])){
			$url = str_replace(':userId',$params['userId'],$url);
			unset($params['userId']);
		}else{
			$url = str_replace(':userId','me',$url);
		}
		
		if(isset($params['start_date'])&&isset($params['end_date'])){
			$url .= '?start_date='.$params['start_date'].'&end_date='.$params['end_date'];
		}
		if(isset($params['detail'])){
			$url .= '&detail='.$params['detail'];
		}
// 		if($resource=='summary'){
// 			var_dump($url);exit;
// 		}
		$headers = array();
		$tk = isset($params['token'])?$params['token']:false;
		if(!$tk){
			$headers=array(
					'app_id: '.$this->_appPar['clientKey'],
					'app_secret: '.$this->_appPar['clientSecret'],
			);
		}else{
			$headers=array(
				'access_token: '.$tk
			);
		};
		
		$opts['headers'] = $headers;
		
		$response = $this->execute('get', $url, '', $opts,$resource);
		return $response;
	}
	
	public function getProfile($params)
	{
		return $this->getResource('profile', $params);
	}
	
	public function getDevice($params)
	{
		return $this->getResource('device', $params);
	}
	
	public function getGoals($params) 
	{
		return $this->getResource('goal', $params);
	}
	
	public function getSummary($params)
	{
		return $this->getResource('summary', $params);
	}
	
	public function getSession($params)
	{
		return $this->getResource('session', $params);
	}
	
	public function getSleep($params)
	{
		return $this->getResource('sleep', $params);
	}
	
	public function saveProfile($params,$family_id)
	{
		$crc32_value = sprintf('%u',crc32($params['name'].$params['birthday'].$params['gender'].$params['avatar'].$params['email']));
		$sql 		= "SELECT crc32_value FROM tb_resource_misifit_profile WHERE userId = '".$params['userId']."' AND identity= '".$family_id."'";
		$result 	= $this->getLocalDb()->ModelQueryRow($sql);
		
		
		if(!$result){
			//插入数据库
			$sql = "INSERT INTO tb_resource_misifit_profile(identity,userId,valid,crc32_value,name,birthday,gender,avatar,email,insert_time) VALUES('%s','%s',1,'%s','%s','%s','%s','%s','%s','%s')";
			
			$sql = sprintf($sql,$family_id,$params['userId'],$crc32_value,$params['name'],$params['birthday'],$params['gender'],$params['avatar'],$params['email'],date('Y-m-d H:i:s'));
			
			$bResult = $this->getLocalDb()->ModelExecute($sql);
			if(!$bResult){
				Yii::log('HealModule::saveProfile():insert ModelExecute() failed,sql is:'.$sql, 'log');
				return false;
			}
			return true;
		}else if($crc32_value == $result['crc32_value']){
			return true;
		}else{
			$sql = "UPDATE tb_resource_misifit_profile SET crc32_value='%s',name='%s',birthday='%s',gender='%s',avatar='%s',email='%s',update_time='%s' WHERE userId = '%s' AND identity = '%s'";
			$sql = sprintf($sql,$crc32_value,$params['name'],$params['birthday'],$params['gender'],$params['avatar'],$params['email'],date('Y-m-d H:i:s'),$params['userId'],$family_id);
			$bResult = $this->getLocalDb()->ModelExecute($sql);
			if(!$bResult){
				Yii::log('HealModule::saveProfile():update ModelExecute() failed,sql is:'.$sql, 'log');
				return false;
			}
			return true;
		}	
		return false;
	}
	
	public function saveDevice($params,$userId)
	{
		$crc32_value = sprintf('%u',crc32($params['id'].$params['deviceType'].$params['serialNumber'].$params['firmwareVersion']
									.$params['batteryLevel'].$params['lastSyncTime']));
		$sql 		= "SELECT crc32_value FROM tb_resource_misifit_device WHERE userId = '".$userId."'";
		$result 	= $this->getLocalDb()->ModelQueryRow($sql);
		
		if(!$result){
			//插入数据库
			$sql = "INSERT INTO tb_resource_misifit_device(userId,deviceId,valid,crc32_value,deviceType,serialNumber,firmwareVersion,batteryLevel,lastSyncTime,insert_time)"
																."VALUES('%s','%s',1,'%s','%s','%s','%s','%d','%s','%s')";

			
			$sql = sprintf($sql,$userId,$params['id'],$crc32_value,$params['deviceType'],$params['serialNumber'],
					$params['firmwareVersion'],$params['batteryLevel'],date('Y-m-d H:i:s',$params['lastSyncTime']),date('Y-m-d H:i:s'));
			
			$bResult = $this->getLocalDb()->ModelExecute($sql);
			if(!$bResult){
				Yii::log('HealModule::saveDevice():insert ModelExecute() failed,sql is:'.$sql, 'log');
				return false;
			}
			return true;
		}else if($crc32_value == $result['crc32_value']){Yii::log('saveDevice end 2','info');
			return 'no change';//同步时间没有改变后边无需处理
		}else{
			$sql = "UPDATE tb_resource_misifit_device SET deviceId='%s',crc32_value='%s',deviceType='%s',serialNumber='%s',firmwareVersion='%s',batteryLevel='%d',lastSyncTime='%s',update_time='%s' WHERE userId='%s'";
			$sql = sprintf($sql,$params['id'],$crc32_value,$params['deviceType'],$params['serialNumber'],
					     $params['firmwareVersion'],$params['batteryLevel'],date('Y-m-d H:i:s',$params['lastSyncTime']),date('Y-m-d H:i:s'),$userId);
			$bResult = $this->getLocalDb()->ModelExecute($sql);
			if(!$bResult){
				Yii::log('HealModule::saveDevice():update ModelExecute() failed,sql is:'.$sql, 'log');
				return false;
			}
			if($result['crc32_value'] == $params['lastSyncTime']){
				return 'no change';
			}
			return true;
		}
	}
	
	public function saveGoals($params,$userId)
	{
		$dataArr = array();
		if(isset($params['goals'])){
			$dataArr = $params['goals'];
		}else{
			$dataArr[]=$params;
		}
		foreach ($dataArr as $row){
			$id = isset($row['id'])?$row['id']:'';
			if($id){
				$sql = "SELECT points FROM tb_resource_misifit_goal WHERE userId = '%s' AND goal_date = '%s'";
				$sql = sprintf($sql,$userId,$row['date']);
				$result 	= $this->getLocalDb()->ModelQueryRow($sql);
				if(!$result){
					$sql = "INSERT INTO tb_resource_misifit_goal (userId,goalsid,points,targetPoints,goal_date,timeZoneOffset,insert_time) "
							."VALUES('%s','%s','%f','%f','%s','%d','%s')";
					$sql = sprintf($sql,$userId,$row['id'],$row['points'],$row['targetPoints'],$row['date'],$row['timeZoneOffset'],date('Y-m-d H:i:s'));
					$bResult = $this->getLocalDb()->ModelExecute($sql);
					if(!$bResult){
						Yii::log('HealModule::saveGoals():insert ModelExecute() failed,sql is:'.$sql, 'log');
						return false;
					}
				}else if($result['points'] == $row['points']){
					continue;
				}else{
					$sql = "UPDATE tb_resource_misifit_goal SET goalsid = '%s',points='%f',targetPoints='%f',update_time='%s' WHERE userId = '%s' AND goal_date = '%s'";
					$sql = sprintf($sql,$row['id'],$row['points'],$row['targetPoints'],date('Y-m-d H:i:s'),$userId,$row['date']);
					$bResult = $this->getLocalDb()->ModelExecute($sql);
					if(!$bResult){
						Yii::log('HealModule::saveGoals():update ModelExecute() failed,sql is:'.$sql, 'log');
						return false;
					}
				}
			}else{
				Yii::log('HealModule::saveGoals() is not have id,and source data is:'.json_encode($params), 'log');
		    }					
		}
		return true;
	}
	
	public function saveSummary($params,$userId)
	{
		$dataArr = $params['summary'];
		foreach ($dataArr as $row){
			$sql = "SELECT steps FROM tb_resource_summary WHERE userId = '%s' AND date = '%s'";
			$sql = sprintf($sql,$userId,$row['date']);
			$result 	= $this->getLocalDb()->ModelQueryRow($sql);
			if(!$result){
				$sql = "INSERT INTO tb_resource_summary(userId,points,steps,calories,activityCalories,distance,date,insert_time)"
										."VALUES('%s','%f','%d','%f','%f','%f','%s','%s');";
				$sql = sprintf($sql,$userId,$row['points'],$row['steps'],$row['calories'],$row['activityCalories']
									,$row['distance'],$row['date'],date('Y-m-d H:i:s'));
				$bResult = $this->getLocalDb()->ModelExecute($sql);
				if(!$bResult){
					Yii::log('HealModule::saveSummary():insert ModelExecute() failed,sql is:'.$sql, 'log');
					return false;
				}
			}else if($result['steps'] == $row['steps']){
				continue;
			}else{
				$sql = "UPDATE tb_resource_summary SET points='%f',steps='%d',calories='%f',activityCalories='%f',distance='%f',update_time='%s' WHERE userId='%s' AND date='%s'";
				$sql = sprintf($sql,$row['points'],$row['steps'],$row['calories'],$row['activityCalories'],$row['distance'],date('Y-m-d H:i:s'),$userId,$row['date']);
				$bResult = $this->getLocalDb()->ModelExecute($sql);
				if(!$bResult){
					Yii::log('HealModule::saveSummary():update ModelExecute() failed,sql is:'.$sql, 'log');
					return false;
				}
			}
		}
		return true;
	}

	
	public function saveSession($params,$userId)
	{
		$dataArr = array();
		if(isset($params['sessions'])){
			$dataArr = $params['sessions'];
		}else{
			$dataArr[]=$params;
		}
		$model = new MongoSession();
		foreach ($dataArr as $row){
			$model->setParam($row,$userId);
			$bResult = $model->addInfo();
			if(!$bResult){
				Yii::log('HealModule::saveSession():addInfo  failed,model is:'.json_decode($model), 'log');
				return false;
			}
		}
		return true;
	}
	
	public function saveSleep($params,$userId)
	{
		$dataArr = array();
		if(isset($params['sleeps'])){
			$dataArr = $params['sleeps'];
		}else{
			$dataArr[]=$params;
		}
		$model = new MongoSleep();
		foreach ($dataArr as $row){
			$model->setParam($row,$userId);
			$bResult = $model->addInfo();
			if(!$bResult){
				Yii::log('HealModule::saveSleep():addInfo  failed,model is:'.json_decode($model), 'log');
				return false;
			}
		}
		return true;
	}
	
	public function saveNotification($params)
	{
		$model = new MongoNotification();
		$model->setParam($params);
		$bResult = $model->addInfo();
		if(!$bResult){
			Yii::log('HealModule::saveNotification():addInfo  failed,model is:'.json_decode($model), 'log');
			return false;
		}
	}
	
	public function dealNotification($params)
	{
		$model = new MongoMessage();
		foreach($params as $row){
			$model->setParam($row);
			$bResult = $model->addInfo();
			if(!$bResult){
				Yii::log('HealModule::dealNotification():addInfo  failed,model is:'.json_decode($model), 'log');
				return false;
			}
			
			$param = array();
			$param['id'] = $row['id'];
			$param['userId'] = $row['ownerId'];
			$type  = $row['type'];
			if($type=='profiles'){				
				$profileData = $this->getProfile($param);
				if(!$profileData || !is_string($profileData)){
					Yii::log('HealModule::dealNotification getProfile fail, return data is:'.$profileData,'info');
					continue;
				}
				$profileArr  = json_decode($profileData,true);
				if(isset($profileArr['code'])){
					Yii::log('HealModule::dealNotification():getProfile json_decode failed,param is:'.json_decode($param).',and return is'.$profileData, 'log');
					continue;
				}
				$result 	 = $this->saveProfile($profileArr,$param['userId']);
				if(!$result){
					Yii::log('HealModule::dealNotification():saveProfile failed,profileData is:'.$profileData, 'log');
					continue;
				}
			}elseif ($type == 'devices'){
				$deviceData = $this->getDevice($param);
				if(!$deviceData || !is_string($deviceData)){
					Yii::log('HealModule::dealNotification getDevice fail, return data is:'.$deviceData,'info');
					continue;
				}
				$deviceArr  = json_decode($deviceData,true);
				if(isset($deviceArr['code'])){
					Yii::log('HealModule::dealNotification():getDevice json_decode failed,param is:'.json_decode($param).',and return is'.$deviceData, 'log');
					continue;
				}
				$result 	 = $this->saveDevice($deviceArr,$param['userId']);
				if(!$result){
					Yii::log('HealModule::dealNotification():saveDevice failed,deviceData is:'.$profileData, 'log');
					continue;
				}
			}elseif($type == 'goals'){
				$goalsData = $this->getGoals($param);
				if(!$goalsData || !is_string($goalsData)){
					Yii::log('HealModule::dealNotification getGoals fail, return data is:'.$goalsData,'info');
					continue;
				}
				$goalsArr  = json_decode($goalsData,true);
				if(isset($goalsArr['code'])){
					Yii::log('HealModule::dealNotification():getGoals json_decode failed,param is:'.json_decode($param).',and return is'.$goalsData, 'log');
					continue;
				}
				$result 	 = $this->saveGoals($goalsArr,$param['userId']);
				if(!$result){
					Yii::log('HealModule::dealNotification():saveGoals  failed,goalsData is:'.$goalsData, 'log');
					continue;
				}
			}elseif ($type == 'sessions'){
				$sessionData = $this->getSession($param);
				if(!$sessionData || !is_string($sessionData)){
					Yii::log('HealModule::dealNotification getSession fail, return data is:'.$sessionData,'info');
					continue;
				}
				$sessionArr  = json_decode($sessionData,true);
				if(isset($sessionArr['code'])){
					Yii::log('HealModule::dealNotification():getSession json_decode failed,param is:'.json_decode($param).',and return is'.$sessionData, 'log');
					continue;
				}
				$result 	 = $this->saveSession($sessionArr,$param['userId']);
				if(!$result){
					Yii::log('HealModule::dealNotification():saveSession  failed,sessionData is:'.$sessionData, 'log');
					continue;
				}
			}elseif($type == 'sleeps'){
				$sleepData = $this->getSleep($param);
				if(!$sleepData || !is_string($sleepData)){
					Yii::log('HealModule::dealNotification getSleep fail, return data is:'.$sleepData,'info');
					continue;
				}
				$sleepArr  = json_decode($sleepData,true);
				if(isset($sleepArr['code'])){
					Yii::log('HealModule::dealNotification():getSleep json_decode failed,param is:'.json_decode($param).',and return is'.$sleepData, 'log');
					continue;
				}
				$result 	 = $this->saveSleep($sleepArr,$param['userId']);
				if(!$result){
					Yii::log('HealModule::dealNotification():saveSleep  failed,sleepData is:'.$sleepData, 'log');
					continue;
				}
			}
			
			/**
			 * 后期可以考虑在此增加通知逻辑
			 */
		}
		return true;
	}
}