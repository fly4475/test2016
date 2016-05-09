<?php
class ApiController extends Controller
{
	/**
	 * 由中控拉取，检查服务可用性，跳转到wash8页面
	 */	
	public function actionThirdparty()
	{
		if($_GET){
			$info               = isset($_GET['info'])?$_GET['info']:'';
			$info_d             = Fnlib::declassify($info);
			if(!$info_d){
				die("Sorry,Parameter parsing error from cc,please check center controller!");
			}
			$infoArr    = json_decode($info_d,true);
			$ccid 		= isset($infoArr['ccid'])?$infoArr['ccid']:'';
			$token		= isset($infoArr['token'])?$infoArr['token']:'';
			$thirdId	= isset($infoArr['third_id'])?$infoArr['third_id']:'';
			$longtitude = isset($infoArr['longtitude'])?$infoArr['longtitude']:'';//113.924747
			$latitude   = isset($infoArr['latitude'])?$infoArr['latitude']:'';//22.52227
			$phone      = isset($infoArr['user_id'])?$infoArr['user_id']:'';
			$name       = isset($infoArr['user_name'])?$infoArr['user_name']:'';
			if($ccid == '' || $token == '' || $thirdId == '' || $longtitude == '' || $latitude == '' || $phone == ''){
				die("Sorry,Parameter missing from cc,please check center controller!");
			}
			
			$apiModel   = new ModelThirdInterface();
			$openid      = $apiModel->checkOpenId($ccid,$thirdId,$phone);
			if($openid === false){
				$openid      = hash('md5',$ccid.$phone.$thirdId);
				$bResult = $apiModel->insertOpenId($openid,$ccid,$thirdId,$phone,$name);
				if($bResult === false){
					Yii::log('ApiController::actionThirdparty():insertOpenId faile,source data is:'.$info_d, 'info');
					$this->render('noservice',array('msg'=>'您所在的位置服务不可用！'));
					exit;
				}
			}
			
			//获取第三方信息
			$thirdInfo = $apiModel->getThirdInfo($thirdId);
			if($thirdInfo === false){
				Yii::log('ApiController::actionThirdparty():getThirdInfo faile,thirdId is:'.$thirdInfo['payment_notify_url'], 'info');
				$this->render('noservice',array('msg'=>'您所在的位置服务不可用！'));
				exit;
			}
			
			//检查服务可用性
			$geoInfo       = json_encode(array(
					'latitude'=>$latitude,
					'longitude'=>$longtitude,
					'is_official'=>0,
					'comefrom'=>'turingcat'
			));
			$geoInfo = Fnlib::enclassify($geoInfo);
			$checkResult   = $apiModel->checkAvailable($thirdInfo,$geoInfo);
			if(!$checkResult){
				Yii::log('ApiController::actionThirdparty checkAvailable is timeout, url is:'.$thirdInfo['service_available_url']);
				$json_result = Fnlib::get_rsp_result(false,'20006','Connect '.$thirdInfo['service_available_url']." is timeout!");
				exit($json_result);
			}
			
			//先解密
			$checkResult_d             = Fnlib::declassify($checkResult);
			Yii::log('ApiController::actionThirdparty checkResult_d , info is:'.$checkResult_d);
			if(!$checkResult_d){
				Yii::log('ApiController::actionThirdparty $checkResult Fnlib::declassify is failure, info is:'.$checkResult);
				$json_result = Fnlib::get_rsp_result(false,'20002','Parameter parsing error!');
				exit($json_result);
			}
			$checkResult   = json_decode($checkResult_d,true);
//  			$checkResult = array("success"=>"true","urlInfo"=>"");
			if(!$checkResult || !$checkResult['success']){
				$this->render('noservice',array('msg'=>'您所在的位置服务不可用！'));
				exit;
			}
						
			$redis_info = '{"token":"'.$token.'","ccid":"'.$ccid.'","third_id":"'.$thirdId.'","payment_notify_url":"'.$thirdInfo['payment_notify_url'].'"}';
			$apiModel->setRedis("THIRD_PARTY_OPENID:".$openid,$redis_info,1800);
			$urlInfo = $checkResult['urlInfo'];//这边需要对urlInfo进行处理
			
			
			$info = json_encode(array('openid'=>$openid,'is_official'=>0,'comefrom'=>'turingcat'));
			$data = Fnlib::enclassify($info);
			if($urlInfo===""){
				$url = $thirdInfo['redirect_html_url'].'?info='.$data;
			}else{
				$url = $thirdInfo['redirect_html_url'].'?'.$urlInfo.'&info='.$data;
			}
			header('Location: '.$url);
			exit;		
		}else{
			$this->render('noservice',array('msg'=>'您所在的位置服务不可用！'));
			exit;
		}
	}
	
	/**
	 * 生成用户订单
	 */
	public function actionGetorder()//下单以后就支付
	{
		if($_REQUEST){//记得改为POST
			$info               =   isset($_REQUEST['info'])?$_REQUEST['info']:'';
			$info_d             =   Fnlib::declassify($info);
			if(!$info_d){
				Yii::log('ApiController::actionGetorder Fnlib::declassify is failure, info is:'.$info);
				$json_result = Fnlib::get_rsp_result(false,'20002','Parameter parsing error!');
				exit($json_result);
			}
			$infoArr            =   json_decode($info_d,true);
			$openid     		=   isset($infoArr['openid'])?$infoArr['openid']:'';
			$order_info 		=   isset($infoArr['order_info'])?$infoArr['order_info']:'';//订单信息的格式
			if($openid == ''|| $order_info==''){
				Yii::log('ApiController::actionGetorder param json_decode is failure, json is:'.$info_d);
				$json_result = Fnlib::get_rsp_result(false,'20003','Parameter missing!');
				exit($json_result);
			}
			$apiModel   		= new ModelThirdInterface();
			$info = $apiModel->getValueInRedis("THIRD_PARTY_OPENID:".$openid);//检查服务可用性时已设置
			if(!$info){
				$json_result = Fnlib::get_rsp_result(false,'60001','Make order timeout!');
				exit($json_result);
			}
			$infoArr            = json_decode($info,true);		
			$order_id   = $apiModel->createExorder();
			if($order_id){
				$result     = array('result'=>true,
						'order_id'=>$order_id,
						'is_official'=>0,
						'comefrom'=>'turingcat'
				);
			}else{
				$result     = array(
						'result'=>false,
						'order_id'=>'',
						'is_official'=>0,
						'comefrom'=>'turingcat'
				);
			}
			
			echo json_encode($result);
			$apiModel->saveOrder($order_id,$infoArr['third_id'],$openid,$order_info);
		}else{
			$json_result = Fnlib::get_rsp_result(false,'20001','Parameter request mode error');
			Yii::log('ApiController::actionGetorder request mode is failure, info is:'.$_REQUEST['info']);
			exit($json_result);
		}
	}
	
	
	/**
	 * 跳转到支付页面
	 */
	public function actionPay()
	{
		$info               =   isset($_REQUEST['info'])?$_REQUEST['info']:'';
		$info_d             =   Fnlib::declassify($info);
		if(!$info_d){
			Yii::log('ApiController::actionGetorder Fnlib::declassify is failure, info is:'.$info);
			$json_result = Fnlib::get_rsp_result(false,'20002','Parameter parsing error!');
			exit($json_result);
		}
		$infoArr            =   json_decode($info_d,true);
		$order_id 	= isset($infoArr['order_id'])?$infoArr['order_id']:'';
		if($order_id == ''){
			Yii::log('ApiController::actionPay param json_decode is failure, json is:'.$info_d);
			$json_result = Fnlib::get_rsp_result(false,'20003','Parameter missing!');
			exit($json_result);
		}
	    $apiModel   = new ModelThirdInterface();
	    $condition  = 'pay_status=0';
		$order_info = $apiModel->getOrederInfo($order_id,$condition);
		if(!$order_info){
			$this->render('noservice',array('msg'=>'订单号不存在，请重新登录！'));
			exit;
		}	
		$money    = $order_info['total_fee'];
		$this->render('pay',array('money'=>$money,'orderid'=>$info));
		exit;		
	}
	
	/**
	 * 与支付中心处理数据交换，完成逻辑处理，实现预扣费
	 */
	public function actionConsume()
	{
		$orderid = isset($_POST['orderid'])?$_POST['orderid']:'';
		$info_d             =   Fnlib::declassify($orderid);
		if(!$info_d){
			Yii::log('ApiController::actionGetorder Fnlib::declassify is failure, info is:'.$info);
			$json_result = Fnlib::get_rsp_result(false,'20002','Parameter parsing error!');
			exit($json_result);
		}
		$infoArr    = json_decode($info_d,true);
		$orderid 	= isset($infoArr['order_id'])?$infoArr['order_id']:'';
		if($orderid == ''){
			Yii::log('ApiController::actionConsume param json_decode is failure, json is:'.$info_d);
			$json_result = Fnlib::get_rsp_result(false,'20003','Parameter missing!');
			exit($json_result);
		}
		$spwd    = isset($_POST['spwd'])?$_POST['spwd']:'';
		$spwd    = str_replace(",", "", $spwd);
		$apiModel   = new ModelThirdInterface();
		$condition  = 'pay_status=0';
		$order_info = $apiModel->getOrederInfo($orderid,$condition);
		if(!$order_info){
			$this->render('noservice',array('msg'=>'订单号不存在，为了您的安全请重新登录！'));
			exit;
		}
		$openid = $order_info['openid'];
		$open_info = $apiModel->getValueInRedis("THIRD_PARTY_OPENID:".$openid);
		if(!$open_info){
			$this->render('noservice',array('msg'=>'订单支付超时，为了您的安全请重新登录！'));
			exit;
		}
		$open_info_arr = json_decode($open_info,true);
// 		$testData = array(	
// 				'token'=>'dddgggfffroyareyouok007',
// 				'passwd' =>'123123',
// 				'authenid'=>'1',
// 				'busynessnum'=>'wash8001',
// 				'orderid'=>'TRC20150602000017',
// 				'feetype'=>'0',
// 				'money'=>'5',
// 				'srcTerminal'=>'1',
// 				'orderDesc'=>'商品描述图灵猫',
// 				'url'=>array(
// 						'subject'  => '订单名称图灵猫',
// 						'show_url' => 'http://www.baidu.com'
// 				),
// 		);
		$testData = array(
				'token'=>$open_info_arr['token'],
				'passwd' =>$spwd,
				'authenid'=>'1',
				'busynessnum'=>$open_info_arr['third_id'],
				'orderid'=>$orderid,
				'feetype'=>'0',
				'money'=>$order_info['total_fee'],
				'srcTerminal'=>'1',
				'orderDesc'=>$order_info['body'],
				'url'=>array(
						'subject'  => $order_info['subject'],
						'show_url' => $order_info['show_url'],
				),
		);
		$testData = json_encode($testData).PHP_EOL;
		$url    = Yii::app()->params['api_pay']['consume_money'];
		
// 		$myinfo = Fnlib::geturldatapost($url, 'info='.$testData);//跳转到支付后台
		$myinfo = json_encode(array('result'=>0));//仅测试环境下使用
		if($myinfo){
			$myinfo = json_decode($myinfo,true);
			
			if($myinfo['result'] == 0)//扣费成功
			{
				/**
				 *增加修改本地订单状态的逻辑
				 */
				$result = $apiModel->updateOrderForPay($orderid);
				$info = json_encode(array('orderid'=>$orderid,'is_success'=>true,'is_official'=>0,'comefrom'=>'turingcat'));
				$data = Fnlib::enclassify($info);
				$myinfo = Fnlib::geturldatapost($open_info_arr['payment_notify_url'], 'info='.$data);
				if($myinfo=='success'){
					$notify =1;//通知成功，后边需要增加通知失败判断的逻辑
				}else{
					$notify =2;//通知失败
				}
				$result = $apiModel->updateOrderForNotice($orderid,$notify);
				
				//跳转回支付成功页面
				$returnInfo = json_encode(array("openid"=>$openid,"orderid"=>$orderid));
				$returnInfo = Fnlib::enclassify($returnInfo);
				header('Location: '.$order_info['return_url'].'?info='.$returnInfo);
				exit;
			}else if($myinfo['result'] == 7501){//余额不足
				$this->render('noservice',array('msg'=>'余额不足，请您至图灵猫用户中心充值！'));
				exit;
			}else if($myinfo['result'] == 4104){//钱包未激活
				$this->render('noservice',array('msg'=>'钱包功能尚未激活，请您至图灵猫用户中心激活！'));
				exit;
			}else if($myinfo['result'] == 4102){//密码错误
				$error_num = $apiModel->getValueInRedis("PWD_ERROR_NUM:".$openid);
				if(!$error_num){
					$error_num =1;
				}else if ($error_num == 2){//目前的策略是重新进入，以后视情况定
					$this->render('noservice',array('msg'=>'密码输错超过3次，为了您的安全请重新登录！'));
					exit;
				}else{
					$error_num = (int)$error_num+1;
				}
				$apiModel->setRedis("PWD_ERROR_NUM:".$openid,$error_num,600);
				$this->render('pwderror',array('orderid'=>$orderid,'msg'=>'密码错误，请重新输入'));
				exit;
			}else {//其他
				$this->render('noservice',array('msg'=>'服务处理异常，为了您的安全请重新登录！'));
				exit;
			}
		}else{
			$this->render('noservice',array('msg'=>'支付服务处理异常，为了您的安全请重新登录！'));
			exit;
		}
	}
	
	/**
	 * 用户确认收货后，第三方反馈用户订单完成，这一步需要通知支付中心解除用户资金冻结状态
	 */
	public function actionFinish()
	{
		if($_REQUEST){	
			$info    = isset($_REQUEST['info'])?$_REQUEST['info']:'';
			$info_d    = Fnlib::declassify($info);
			if(!$info_d){
				Yii::log('ApiController::actionFinish Fnlib::declassify is failure, info is:'.$info);
				$json_result = Fnlib::get_rsp_result(false,'20002','Parameter parsing error!');
				exit($json_result);
			}
			$infoArr = json_decode($info_d,true);
			$access_token	= isset($infoArr['access_token'])?$infoArr['access_token']:'';
			$status         = isset($infoArr['status'])?$infoArr['status']:'';
			$orderid        = isset($infoArr['order_id'])?$infoArr['order_id']:'';
			$source         = isset($infoArr['source'])?$infoArr['source']:0;//默认0来源中控,1为PC
			if($access_token == '' || $status == '' || $orderid == ''){
				Yii::log('ApiController::actionFinish param json_decode is failure, json is:'.$info_d);
				$json_result = Fnlib::get_rsp_result(false,'20003','Parameter missing!');
				exit($json_result);
			}
			$apiModel   = new ModelThirdInterface();
			//获取订单信息
			$condition  = 'pay_status>0';
			$order_info = $apiModel->getOrederInfo($orderid,$condition);
			if(!$order_info){
				$json_result = Fnlib::get_rsp_result(false,'60002','The order does not exist!');
				exit($json_result);
			}
			
			//检查access_token
			$bResult 		= $apiModel->compareRedis("THIRD_PARTY_TOKEN:".$order_info['tid'],$access_token);
			if(!$bResult){//token校验失败
				$json_result = Fnlib::get_rsp_result(false,'4001','Authentication failure!');
				exit($json_result);
			}
						
			$openid = $order_info['openid'];
			if($source && $status==1){//1来源PC,且为退款操作
				$result = $apiModel->getUserInfo($openid);
				if(!$result){
					$json_result = Fnlib::get_rsp_result(false,'20004','The user information does not exist!');
					exit($json_result);
				}
				$open_info_arr = array('token'=>$result['phone']);
			}else{//来源中控
				$open_info = $apiModel->getValueInRedis("THIRD_PARTY_OPENID:".$openid);
				if(!$open_info){
					$json_result = Fnlib::get_rsp_result(false,'60001','Make order timeout!');
					exit($json_result);
				}
				$open_info_arr = json_decode($open_info,true);
			}
			
			
			
			/**
			 * 通知支付中心，扣除预付费的解冻金额
			 */		
			$url =Yii::app()->params['api_pay']['post_consume'];
// 			$testData = array(
// 					'token'=>'dddgggfffroyareyouok007',
// 					'authenid'=>'1',
// 					'busynessnum'=>'wash8001',
// 					'orderid'=>'TRC20150602000017',
// 					'money'=>'5',
// 					'acktype'=>'1',//1为取消，9为确认
// 					'orderDesc'=>'商品描述图灵猫',
// 					'url'=>array(
// 							'subject'  => '订单名称图灵猫',
// 							'show_url' => 'http://www.baidu.com'
// 					),
// 					'reasondesc'=>'不想洗车了',
// 					'ackreason'=>'0',
// 			);
			$testData = array(
					'token'=>$open_info_arr['token'],
					'authenid'=>'1',
					'busynessnum'=>$order_info['tid'],
					'orderid'=>$orderid,
					'money'=>$order_info['total_fee'],
					'acktype'=>$status,//1为取消，9为确认
					'orderDesc'=>$order_info['body'],
					'url'=>array(
							'subject'  => $order_info['subject'],
							'show_url' => $order_info['show_url']
					),
					'reasondesc'=>'不想洗车了',
					'ackreason'=>'0',
					'source'=> $source
			);
			$testData = json_encode($testData).PHP_EOL;
// 			$myinfo = Fnlib::geturldatapost($url, 'info='.$testData);
// 			$myinfoArr = json_decode($myinfo,true);
			$myinfoArr = array('result'=>0);
			if($myinfoArr&&$myinfoArr['result']==0){//成功
				/**
				 * 修改本地订单状态
				 */
				$result = $apiModel->updateOrder($orderid,$status);
				if($result === false){
					$json_result = Fnlib::get_rsp_result(false,'40005','Data processing exception!');
					exit($json_result);
				}else{
					$json_result = Fnlib::get_rsp_result(true,'00000','Success');
					echo ($json_result);
				}
			}else{
				$json_result = Fnlib::get_rsp_result(false,'40005','Data processing exception!');
				exit($json_result);
			}
			
		}else{
			$json_result = Fnlib::get_rsp_result(false,'20002','POST DATA ERROR');
			exit($json_result);
		}
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	/**
	 * 获取access_token
	 */
	public function actionGettoken()
	{
		if($_GET){
			$thirdId	= isset($_GET['third_id'])?$_GET['third_id']:'';
			$secretKey	= isset($_GET['secret_info'])?$_GET['secret_info']:'';
			if($thirdId ==''|| $secretKey == ''){
				$json_result = Fnlib::get_rsp_result(false,'20003','Parameter missing!');
				exit($json_result);
			}
			$thirdId    = Fnlib::sql_check_str($thirdId);
			$apiModel   = new ModelThirdInterface();
			$strResult 	= $apiModel->getSecretKey($thirdId);
			$nResult  	=  $apiModel->checkSecretKey($secretKey,$strResult);
			if($nResult === false){
				$json_result = Fnlib::get_rsp_result(false,'4001','Authentication failure!');
				exit($json_result);
			}
			$generateToken = $apiModel->getString16($thirdId);
			$expire_in     = Yii::app()->params['expire_in'];
			$result = array('result'		=> true,
					'access_token'	=>$generateToken,
					'expire_in'   	=>$expire_in,
			);
			echo json_encode($result);
			$apiModel->setRedis("THIRD_PARTY_TOKEN:".$thirdId,$generateToken,$expire_in);
			exit;
		}else{
			$json_result = Fnlib::get_rsp_result(false,'20001','Parameter request mode error!');
			exit($json_result);
		}
	
	}
	
	/**
	 * 获取用户信息
	 */
	public function actionGetuserinfo()
	{
		if($_GET){
			$info    = isset($_GET['info'])?$_GET['info']:'';
			$info_d    = Fnlib::declassify($info);
			if(!$info_d){
				Yii::log('ApiController::actionGetuserinfo Fnlib::declassify is failure, info is:'.$info);
				$json_result = Fnlib::get_rsp_result(false,'20002','Parameter parsing error!');
				exit($json_result);
			}
			$infoArr = json_decode($info_d,true);
			$access_token	= isset($infoArr['access_token'])?$infoArr['access_token']:'';			
			$openid			= isset($infoArr['openid'])?$infoArr['openid']:'';
			if($access_token ==''|| $openid == ''){
				$json_result = Fnlib::get_rsp_result(false,'20003','Parameter missing!');
				exit($json_result);
			}
			$openid         = Fnlib::sql_check_str($openid);
			$apiModel   	= new ModelThirdInterface();
			$result        	= $apiModel->GetUserByOpenid($openid);
			if($result === false){//openid有误
				$json_result = Fnlib::get_rsp_result(false,'50001','User information does not exist');
				exit($json_result);
			}			
			$tid   = $result['tid'];
			$cid   = $result['cid'];
			$phone = $result['phone'];
			$name  = $result['name'];
			
			$bResult 		= $apiModel->compareRedis("THIRD_PARTY_TOKEN:".$tid,$access_token);
			if(!$bResult){//token校验失败
				$json_result = Fnlib::get_rsp_result(false,'4001','Authentication failure!');
				exit($json_result);
			}
						
			$result = array(
								'result'=>true,
						    	'baseInfo'=>array('phone'=>$phone,'name'=>$name),
								'furtherInfo'=>array('country'=>'中国','province'=>'广东省','city'=>'深圳市',
													'community'=>'鼎太风华一期','building'=>'3栋','room'=>'505')//目前前模拟出住址信息	        
					        );
			
			exit(json_encode($result));
		}else{
			$json_result = Fnlib::get_rsp_result(false,'20001','Get parameter error!');
			exit($json_result);
		}
	}
	

	
	/**
	 * 接收第三方通知消息
	 */	
	public function actionReceivemsg()
	{
		if($_REQUEST){
			$info    = isset($_REQUEST['info'])?$_REQUEST['info']:'';
			$info_d    = Fnlib::declassify($info);Yii::log('ApiController::actionReceivemsg Fnlib::declassify is failure, info is:'.$info_d);
			if(!$info_d){
				Yii::log('ApiController::actionReceivemsg Fnlib::declassify is failure, info is:'.$info);
				$json_result = Fnlib::get_rsp_result(false,'20002','Parameter parsing error!');
				exit($json_result);
			}
			$infoArr = json_decode($info_d,true);
			$access_token	= isset($infoArr['access_token'])?$infoArr['access_token']:'';			
			$orderid	    = isset($infoArr['orderid'])?$infoArr['orderid']:'';
			$title          = isset($infoArr['title'])?$infoArr['title']:'';
			$content        = isset($infoArr['content'])?$infoArr['content']:'';
			$msgtype        = isset($infoArr['msgtype'])?$infoArr['msgtype']:'';
			if($access_token ==''|| $orderid == '' || $title == '' || $content == '' || $msgtype == ''){
				$json_result = Fnlib::get_rsp_result(false,'20003','Parameter missing!');
				exit($json_result);
			}
			
			$apiModel   = new ModelThirdInterface();
			$condition  = '( pay_status=1 OR pay_type=10 )';//支付成功或现金支付
			$order_info = $apiModel->getOrederInfo($orderid,$condition);
			if(!$order_info){
				$json_result = Fnlib::get_rsp_result(false,'60002','The order does not exist!');
				exit($json_result);
			}
			
			$bResult 		= $apiModel->compareRedis("THIRD_PARTY_TOKEN:".$order_info['tid'],$access_token);
			if(!$bResult){//token校验失败
				$json_result = Fnlib::get_rsp_result(false,'4001','Authentication failure!');
				exit($json_result);
			}
			
			$result = array(
					'result'=>true,
					'msg'=>'这里以后留着扩展'
			);				
			echo json_encode($result);
			
			$apiModel->saveThirdMsg($infoArr);
			exit;
		}else{
			exit("fail");
		}
	}
	
	
	
	
	
	
	/**
	 * 查询用户余额
	 */
	public function actionQuery()
	{
// 		$url     = 'http://172.16.45.45:18888/api/query/money';
		$url     = Yii::app()->params['api_pay']['query_money'];
		$testData = array(
				'token'=>'tttttttyyyyyyyyyoopp007',//'11b1ba5b1cc94ef584cfe277306f12ab',//'e8a03bfed8034663ae9e7a4053ae29a7'
				'authenid'=>'1',
		);
		$testData = json_encode($testData).PHP_EOL;
		$myinfo = Fnlib::geturldatapost($url, 'info='.$testData);
		if($myinfo){
			$myinfo = json_decode($myinfo,true);
			var_dump($myinfo);
		}else{
			echo 'error';
		}
	}
	
	
}