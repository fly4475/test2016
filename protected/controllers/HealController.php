<?php
class HealController extends Controller
{
	public function actionLogin()
	{
		/**
		 * 先从中控拿到用户的identity
		 */
		$param = isset($_GET["family_id"])?$_GET["family_id"]:"";
		if(!$param){
			die("param empty!");
		}else{
			$misfitApi = new HealModule();
			$url       = $misfitApi->authorize($param);
			header("Location: ".$url);//调用被动接口
			exit;
		}		
	}
	
	
	/**
	 * 被动接口
	 */
	public function actionHome()
	{
		try{
			$code      = isset($_GET['code'])?$_GET['code']:'';
			$family_id = isset($_GET['family_id'])?$_GET['family_id']:'';
			if($code=="" || $family_id == ""){
				Yii::log('HealController::actionHome get data is empty.','info');
				exit("param empty,fail!");
			}
			$misfitApi = new HealModule();
			$result = $misfitApi->exchange($code,$family_id);
			if(!$result){
				Yii::log('HealController::actionHome exchange fail,return data is '.$result,'info');
				exit("exchange fail");
			}
			$arr = json_decode($result,true);
			if($arr && isset($arr['access_token'])){
				//获取profile
				$param = array('token'=>$arr['access_token']);
				$profileData = $misfitApi->getProfile($param);
				if(!$profileData){
					Yii::log('HealController::actionHome getProfile fail, return data is:'.$profileData,'info');
					die('profile get data error!');
				}
				$profileArr = json_decode($profileData,true);
				if($profileArr && isset($profileArr['userId'])){
					$result = $misfitApi->saveProfile($profileArr,$family_id);
					if(!$result){
						die('profile deal data error!');
					}
			
					//获取device,如果最后一次同步时间没有更新，以下的步骤可以不用走
					$deviceData  = $misfitApi->getDevice($param);
					if(!$deviceData){
						Yii::log('HealController::actionHome getDevice fail, return data is:'.$deviceData,'info');
						die('device get data error!');
					}Yii::log('getDevice:'.$deviceData,'info');
					$deviceArr   = json_decode($deviceData,true);
					$deviceId    = $deviceArr['id'];
					$result 	 = $misfitApi->saveDevice($deviceArr,$profileArr['userId']);
					if(!$result){
						die('device deal data error!');
					}
					if($result === true){
						$param['start_date']  = date('Y-m-d',time()-86400*30);
						$param['end_date']	  = date('Y-m-d');
							
						//获取goal
						$goalData    = $misfitApi->getGoals($param);
						if(!$goalData){
							Yii::log('HealController::actionHome getGoals fail, return data is:'.$goalData,'info');
							die('goal get data error!');
						}
						$goalArr     = json_decode($goalData,true);
						$result 	 = $misfitApi->saveGoals($goalArr,$profileArr['userId']);
						if(!$result){
							die('goal deal data error!');
						}
							
						//获取summary
						$summartPar  = $param;
						$summartPar['detail'] = 'true';
						$summaryData = $misfitApi->getSummary($summartPar);
						if(!$summaryData){
							Yii::log('HealController::actionHome getSummary fail, return data is:'.$summaryData,'info');
							die('summary get data error!');
						}
						$summaryArr  = json_decode($summaryData,true);
						$result 	 = $misfitApi->saveSummary($summaryArr,$profileArr['userId']);
						if(!$result){
							die('summary deal data error!');
						}
							
						//获取Session
						$sessionData = $misfitApi->getSession($param);
						if(!$sessionData){
							Yii::log('HealController::actionHome getSession fail, return data is:'.$sessionData,'info');
							die('session get data error!');
						}
						$sessionArr  = json_decode($sessionData,true);
						$result 	 = $misfitApi->saveSession($sessionArr,$profileArr['userId']);
						if(!$result){
							die('Session deal data error!');
						}
							
						//获取Sleep
						$sleepData 	 = $misfitApi->getSleep($param);
						if(!$sleepData){
							Yii::log('HealController::actionHome getSleep fail, return data is:'.$sleepData,'info');
							die('sleep get data error!');
						}
						$sleepArr    = json_decode($sleepData,true);
						$result 	 = $misfitApi->saveSleep($sleepArr,$profileArr['userId']);
						if(!$result){
							die('Session deal data error!');
						}
					}
					
					/**
					 * 跳转到健康管理首页
					 */
					$direct_url = Yii::app()->params['heal_config']['third_info']['direct_url'].'?family_id='.$family_id.'&deviceId='.$deviceId;
					header("Location: ".$direct_url);
					//  				die('All Success!');
					
				}else{
					Yii::log('HealController::actionHome getProfile data is empty.','info');
					exit("getProfile fail");
				}
			}else{
				die($result);
			}
			
			
			
		}catch(Exception $e){
			die($e);
		}
   		
		
	}

	/**
	 * 订阅者接口
	 */
	public function actionHeal()
	{
		$body      	     = @file_get_contents('php://input');
		Yii::log('HealController::actionHeal resource body data is :'.$body,'info');
		
		$bodyArr 		 = json_decode($body,true);
		if(!$bodyArr){
			Yii::log('HealController::actionHeal body data is fail, data is:'.$body,'info');
			exit("fail");
		}
		
		
		//第一次认证时逻辑处理
		if($bodyArr['Type'] == 'SubscriptionConfirmation'){
			Yii::log('HealController::actionHeal SubscriptionConfirmation starting...,and url is:'.$bodyArr['SubscribeURL'],'info');			
			$myinfo = file_get_contents($bodyArr['SubscribeURL']);
			Yii::log('HealController::actionHeal get content form SubscribeURL,and return date is:'.json_encode($myinfo),'info');
			Yii::log('HealController::actionHeal SubscriptionConfirmation ending...,and url is:'.$bodyArr['SubscribeURL'],'info');
			exit($myinfo);
		}
		
		//订阅消息接口
		if($bodyArr['Type'] == 'Notification'){				
			/**
			 * 先保存Notification
			 */
			$misfitApi = new HealModule();
			$bResult = $misfitApi->saveNotification($bodyArr);
			if(!$result){
				die('Notification Save data error!');
			}

			/**
			 * 开始处理Notification
			 */
			$myinfo = $bodyArr['Message'];
			$bResult = $misfitApi->dealNotification($myinfo);
			if(!$result){
				die('Notification deal data error!');
			}
		}
			
				
		echo date('Y-m-d H:i:s');		
	}
	
	
	/**
	 * 测试接口
	 */
	public function actionTest()
	{
		$params = array();
		$params['id'] = '5678bcf023c878f3a3efee62';
		$params['userId'] = '56710db1e8436c447d0b45f3';
		
		$misfitApi = new HealModule();
		$testData = $misfitApi->getGoals($params);
		$testArr  = json_decode($testData,true);
		$misfitApi->saveGoals($testArr,$params['userId']);die('success');
	}
}