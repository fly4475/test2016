<?php 
class ModelThirdInterface
{
	private $_memredis;
	private $_localDb;
	private $_exDb;
	private $orderTb  = '';
	
	public function getSecretKey($thirdId)
	{
		$sql 		= "SELECT secretkey FROM info_third_secret WHERE nameid = '".$thirdId."'";
		$strResult 	= $this->getLocalDb(Yii::app()->db)->ModelQueryRow($sql);
		if($strResult === false){
			return false;
		}else{
			return $strResult['secretkey'];
		}
		return $strResult;
	}
	
	public function checkSecretKey($strGet,$strSelect)
	{
		$url_exp = Fnlib::declassify($strGet);
		if(!$url_exp){
			Yii::log('ModelThirdInterface::checkSecretKey Fnlib::declassify is failure, str is:'.$strGet);
			$json_result = Fnlib::get_rsp_result(false,'20002','Parameter parsing error!');
			exit($json_result);
		}
		if($url_exp === $strSelect){
			return true;
		}else{
			return false;
		}
	}
		
	public function getString16($thirdId)
	{
		$password = md5($thirdId+Fnlib::create_guid());
		return substr($password, 0,16);
	}
	
	public function checkOpenId($cid,$thirdId,$phone)
	{
		$sql = "SELECT openid FROM info_user_openid WHERE cid = '".$cid."' AND tid = '".$thirdId."' AND phone = '".$phone."'";
		$result 	= $this->getLocalDb(Yii::app()->db)->ModelQueryRow($sql);
		if($result === false){
			return false;
		}else{
			return $result['openid'];
		}
	}
	
	public function getUserInfo($openid)
	{
		$sql = "SELECT tid,phone FROM info_user_openid WHERE openid = '".$openid."'";
		$result 	= $this->getLocalDb(Yii::app()->db)->ModelQueryRow($sql);
		if($result === false){
			return false;
		}else{
			return $result;
		}
	}
	
	public function insertOpenId($openid,$cid,$thirdId,$phone,$name)
	{
		$sql = sprintf("INSERT INTO info_user_openid (openid,cid,tid,phone,name) VALUES('%s','%s','%s','%s','%s')",$openid,$cid,$thirdId,$phone,$name);
		$result 	= $this->getLocalDb(Yii::app()->db)->ModelExecute($sql);
		return $result;
	}
	
	public function setRedis($k,$v,$t)
	{
		$this->getMemRedis()->setRedis($k,$v,$t);
	}
	
	public function checkKeyInRedis($token)
	{
		$bReslult = $this->getMemRedis()->exists($token);
		return $bReslult;
	}
	
	public function compareRedis($source,$des)
	{
		$bReslult = $this->getMemRedis()->getRedis($source);
		if(!$bReslult){
			return false;
		}else{
			if($bReslult == $des){
				return true;
			}else{
				return false;
			}
		}
	}
	
	public function getValueInRedis($key){
		return $this->getMemRedis()->getRedis($key);
	}
	
	public function delRedis($key)
	{
		$this->getMemRedis()->delete($key);
	}
	
	private function getMemRedis()
	{
		if (!$this->_memredis) {
			$this->_memredis = Yii::app()->redis_cache;
		}
		return $this->_memredis;
	}
	
	private function getLocalDb($db)
	{
		if(!$this->_localDb){
			$this->_localDb = new MysqlManager($db);
		}
		return $this->_localDb;
	}
	
	private function getExDb($db)
	{
		if(!$this->_exDb){
			$this->_exDb = new MysqlManager($db);
		}
		return $this->_exDb;
	}
	
	public function delCheckInfo($check_info)
	{
		$decodeData  = Fnlib::declassify($check_info);
		return $decodeData;
	}
	
	
	
	public function checkCode($openid,$code)
	{
		$getCode  = $this->getMemRedis()->getRedis($openid);
		if($getCode === NULL){
			return false;
		}
		if($getCode == $code){
			return true;
		}
		return false;
	}
	
	public function GetUserByOpenid($openid)
	{
		$sql  		= "SELECT cid,tid,phone,name FROM info_user_openid WHERE openid = '{$openid}'";
		$result 	= $this->getLocalDb(Yii::app()->db)->ModelQueryRow($sql);
		if($result === false){
			return false;
		}else{
			return $result;
		}
	}
	
	public function encodeContent($content)
	{
		$encodeData = Fnlib::enclassify($content);
		return $encodeData;
	}
	
	public function getThirdInfo($thirdId){
		$sql = "SELECT service_available_url,redirect_html_url,payment_notify_url FROM info_third_secret WHERE nameid = '".$thirdId."'";
		$bResult = $this->getLocalDb(Yii::app()->db)->ModelQueryRow($sql);
		if(!$bResult){
			Yii::log('ExorderDb::checkAvailable():get service_url failed,thirdId is:'+$thirdId, 'log');
			return false;
		}
		return $bResult;
	}
	
	public function checkAvailable($thirdInfo,$data)
	{
		$myinfo = Fnlib::geturldatapost($thirdInfo['service_available_url'], 'info='.$data);
		return $myinfo;
	}
	
	public function createExorder()
	{
		try{
			$this->getLocalDb(Yii::app()->db)->lockTable('order_total');

			$strNow =  date("Y-m-d");
			$exorder = $this->getExorder();
	

			$nExorder = 1;
			if(!$exorder){
				$sql = sprintf("INSERT INTO order_total(exordernum,date,insert_time)VALUES('%s','%s','%s')",$nExorder,$strNow,date('Y-m-d H:i:s'));
				$bResult = $this->getLocalDb(Yii::app()->db)->ModelExecute($sql);
				if(!$bResult){
					Yii::log('ExorderDb::createExorder():saveExorder() failed', 'log');
					$this->unlockTables();
					return false;
				}
			}else{
				if ($exorder['date'] == $strNow){
					$nExorder = $exorder['exordernum'] + 1;
				}
				$sql = sprintf("UPDATE order_total SET exordernum = %d, date = '%s',update_time='%s'",$nExorder,$strNow,date('Y-m-d H:i:s'));
				$bResult = $this->getLocalDb(Yii::app()->db)->ModelExecute($sql);
				if(!$bResult){
					Yii::log('ExorderDb::createExorder():updateExorder() failed', 'log');
					$this->getLocalDb(Yii::app()->db)->unlockTables();
					return false;
				}
			}
			$this->getLocalDb(Yii::app()->db)->unlockTables();
				
			$strExorder = $this->getNewExorder($nExorder);
		}catch(Exception $e){
			Yii::log('ExorderDb::createExorder() error:'.$e->getMessage(), 'log');
			return false;
		}
		return $strExorder;
	}

	public function getExorder(){
		try {
				$sql = "SELECT * FROM order_total ";
				$rows = $this->getLocalDb(Yii::app()->db)->ModelQueryRow($sql);
				if($rows === false){
					return false;
				}				
				return $rows;
		}catch(Exception $e){
			Yii::log('ExorderDb::getExorder() error:'.$e->getMessage(), 'log');
			return false;
		}
		return $rows;
	}
	
	public function getNewExorder($nExorder){
		$num 			 = sprintf("%06d", $nExorder);
		$strDate		 = date('Ymd');
		$strExorder		 = 'TRC'.$strDate.$num;
		return $strExorder;
	}
	
	public function saveOrder($order_id,$third_id,$openid,$order_info){
		if(!is_array($order_info)){
			$order_info = json_decode($order_info,true);
		}
		$subject  	= isset($order_info['subject'])?$order_info['subject']:'';
		$total_fee  = isset($order_info['total_fee'])?$order_info['total_fee']:0.01;
		$body  		= isset($order_info['body'])?$order_info['body']:'';
		$show_url  	= isset($order_info['show_url'])?$order_info['show_url']:'';
		$return_url = isset($order_info['return_url'])?$order_info['return_url']:'';
		$paytype    = isset($order_info['pay_type'])?$order_info['pay_type']:3;//3为钱包余额支付,10为现金支付
		$is_official= isset($order_info['is_official'])?$order_info['is_official']:0;//0为测试数据,1为正式数据
		$sql = sprintf("INSERT INTO order_detail(orderid,tid,openid,subject,total_fee,body,show_url,return_url,pay_type,is_official)VALUES('%s','%s','%s','%s','%f','%s','%s','%s','%d','%d')",
													$order_id,$third_id,$openid,$subject,$total_fee,$body,$show_url,$return_url,$paytype,$is_official);
		$bResult = $this->getLocalDb(Yii::app()->db)->ModelExecute($sql);
		if(!$bResult){
			Yii::log('ModelThirdInterface::saveOrder sql failed,sql is', 'log');
			return false;
		}
		return true;
	}
	
	public function saveThirdMsg($infoArr)
	{
		$sql = sprintf("INSERT INTO msg_third_send(orderid,title,content,msgtype,insert_time)VALUES('%s','%s','%s',%d,'%s');",
				$infoArr['orderid'],$infoArr['title'],$infoArr['content'],$infoArr['msgtype'],date('Y-m-d H:i:s'));
		$bResult = $this->getLocalDb(Yii::app()->db)->ModelExecute($sql);
		if(!$bResult){
			Yii::log('ModelThirdInterface::saveThirdMsg() sql failed,sql is:'.$sql, 'log');
			return false;
		}
		return true;
	}
	
	public function saveRecharge($dataArr)
	{
		$subject  	= $dataArr['order_info']['subject'];
		$total_fee  = $dataArr['order_info']['total_fee'];
		$body  		= $dataArr['order_info']['body'];
		$return_url = $dataArr['order_info']['return_url'];
		$order_id   = $dataArr['order_info']['orderid'];
		$show_url   = $dataArr['order_info']['show_url'];
		$authenid   = $dataArr['authenid'];
		$platno     = $dataArr['platno'];
		$name       = $dataArr['name'];
		$token      = $dataArr['token'];
		$sql = sprintf("INSERT INTO recharge_detail(orderid,authenid,name,subject,total_fee,body,platno,return_url,charge_token,show_url)VALUES('%s','%s','%s','%s',%f,'%s','%s','%s','%s','%s')",
				$order_id,$authenid,$name,$subject,$total_fee,$body,$platno,$return_url,$token,$show_url);
		$bResult = $this->getLocalDb(Yii::app()->db)->ModelExecute($sql);
		if(!$bResult){
			Yii::log('ModelThirdInterface::saveRecharge():insert sql failed: '.$sql, 'log');
			return false;
		}
		return true;
	}
	
	public function jumpPayment($order_info,$paytype)
	{
		$arrData = array(
				'WIDout_trade_no'=>	$order_info['orderid'],
				'WIDsubject'     => $order_info['subject'],
				'WIDtotal_fee'   => $order_info['total_fee'],
				'WIDbody'    	 => $order_info['body'],
				'WIDshow_url'    => isset($order_info['show_url'])?$order_info['show_url']:'',
		);
	
		$encodeContent   = $this->encodeContent(json_encode($arrData));
		$url             = Yii::app()->params['third_api_interface'][$paytype];
	
		Fnlib::jumppost($url, array('info'=>$encodeContent));
	}
	
	public function updateOrder($order_id,$status=1){
		$sql = "UPDATE order_detail SET is_receipt = %d,update_time = '%s' WHERE orderid = '%s' AND pay_status>0";
		$sql = sprintf($sql,$status,date("Y-m-d H:i:s"),$order_id);
		$bResult = $this->getLocalDb(Yii::app()->db)->ModelExecute($sql);
		if(!$bResult){
			Yii::log('ModelThirdInterface::createExorder():updateExorder() failed', 'log');
			return false;
		}
		return true;
	}
	
	public function updateOrderForPay($orderid){
		$sql = "UPDATE order_detail SET pay_status = 1,update_time = '%s' WHERE orderid = '%s' ";
		$sql = sprintf($sql,date("Y-m-d H:i:s"),$orderid);
		$bResult = $this->getLocalDb(Yii::app()->db)->ModelExecute($sql);
		if(!$bResult){
			Yii::log('ExorderDb::createExorder():updateExorder() failed', 'log');
			return false;
		}
		return true;
	}
	
	public function updateOrderForNotice($orderid,$notify){
		$sql = "UPDATE order_detail SET is_notify = %d,update_time = '%s' WHERE orderid = '%s' ";
		$sql = sprintf($sql,$notify,date("Y-m-d H:i:s"),$orderid);
		$bResult = $this->getLocalDb(Yii::app()->db)->ModelExecute($sql);
		if(!$bResult){
			Yii::log('ExorderDb::createExorder():updateExorder() failed', 'log');
			return false;
		}
		return true;
	}
	
	public function getOrederInfo($orderid,$condition)
	{
		$sql = "SELECT subject,total_fee,body,show_url,return_url,openid,tid FROM order_detail WHERE orderid='%s' AND %s";
		$sql = sprintf($sql,$orderid,$condition);
		$strResult 	= $this->getLocalDb(Yii::app()->db)->ModelQueryRow($sql);
		return $strResult;
	}
}