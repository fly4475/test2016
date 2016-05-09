<?php
require_once 'lib/DBManager.lib.php';
require_once 'lib/WriteLog.lib.php';
require_once 'Records/ReturnRecord.class.php';
require_once 'Records/NotifyRecord.class.php';
require_once 'configs/config.php';

class PayResponce extends DBManager{
	private $_statis;
	private $_mongo;
	function __construct(){

		$this->_statis = new ReturnRecord();
	
		global $g_arr_db_config;
		$this->connectMySqlPara($g_arr_db_config);
	}
	
	public function recordRequest(){
		$this->_statis->setParam();
		$result = $this->queryOrderid();
		$trade_status = $this->_statis->getTradeStatus();
		if($result){			
			if($trade_status!=$result['0']['trade_status']){
				$this->updateOrderRecord();
			}
		}else{
				$this->insertOrderRecord();
		}
		/**
		 * 以下跳转到第三方支付成功的页面，进行相应的逻辑处理
		 */
		//交易状态
		if($trade_status == 'TRADE_FINISHED' ||$trade_status == 'TRADE_SUCCESS') {
			//判断该笔订单是否在商户网站中已经做过处理
			//如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
			//如果有做过处理，不执行商户的业务程序
			/**
			 * 支付成功，域名重定向
			 */
			$result = $this->queryOrderInfo();
// 			$testData = array(
// 					'authenid'=>$result[0]['authenid'],
// 					'platno'   =>$result[0]['platno'],
// 					'thirdRecordID'=>$result[0]['orderid'],
// 					'money' =>$result[0]['total_fee'],
// 					'url'=>array(
// 							'subject'  => $result[0]['subject'],
// 							'body'     => $result[0]['body'],
// 							'show_url' => $result[0]['show_url'],
// 					),
// 					'chargeToken'=>$result[0]['charge_token']
// 			);
// 			$dataInfo = urlencode(json_encode($testData));
// 			$return_url = $result[0]['return_url'].'?info='.$dataInfo;
			$return_url = $result[0]['return_url'];
			echo "<script language='javascript'>window.location='".$return_url."'</script>";
			exit;
		}
		else {
			/**
			 * 支付失败，这里可以跳转付款失败后的页面
			 */
			echo 'PC Pay Failure: '.$trade_status;
			exit;
		}
	}
	
	private function queryOrderInfo()
	{
		$sql = $this->_statis->getQueryOrderInfoSql();
		$result = $this->executeQuery($sql);
		if($result === false){
			Log::write("PayResponce::queryOrderid():executeQuery() sql: ".$sql." failed", "log");
			return false;
		}
		return $result;
	}
	
	private function queryOrderid(){
		$sql = $this->_statis->getQuerySql();
		$result = $this->executeQuery($sql);
		if($result === false){
//			Log::write("PayResponce::queryOrderid():executeQuery() sql: ".$sql." failed", "log");
			return false;
		}
		return $result;
	}
	
	private function insertOrderRecord()
	{
		$sql = $this->_statis->getInsertSql();
		$result = $this->executeSql($sql);
		if(!$result){
			Log::write("PayResponce::insertOrderRecord():executeSql() sql: ".$sql." failed", "log");
			return false;
		}
		return true;
	}
	
	private function updateOrderRecord()
	{
		$sql = $this->_statis->getUpdateSql();
		$result = $this->executeSql($sql);
		if(!$result){
			Log::write("PayResponce::updateOrderRecord():executeSql() sql: ".$sql." failed", "log");
			return false;
		}
		return true;
	}
	
	private function getRealStatus()
	{
		$sql = $this->_statis->getRealTradeStatusSql();
		$result = $this->executeQuery($sql);
		if(!$result){
			Log::write("PayResponce::getRealStatus():executeSql() sql: ".$sql." failed", "log");
			return false;
		}
		switch ($result[0]['pay_status']){
			case '1':
				return 'TRADE_SUCCESS';
				break;
			case '2':
				return 'TRADE_FINISHED';
				break;
			default:
				return false;
				break;
		}
	}
	
	private function updatePayTimeAndStatus()
	{
		$sql = $this->_statis->getUpdatePayTimeAndStatusSql();
		$result = $this->executeSql($sql);
		if(!$result){
			Log::write("PayResponce::updatePayTimeAndStatus():executeSql() sql: ".$sql." failed", "log");
			return false;
		}
	}
	
	private function updatePayStatus()
	{
		$sql = $this->_statis->getUpdatePayStatusSql();
		$result = $this->executeSql($sql);
		if(!$result){
			Log::write("PayResponce::updatePayStatus():executeSql() sql: ".$sql." failed", "log");
			return false;
		}
	}
	
}