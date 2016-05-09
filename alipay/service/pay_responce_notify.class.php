<?php
require_once 'lib/DBManager.lib.php';
require_once 'lib/WriteLog.lib.php';
require_once 'lib/Fnlib.php';
require_once 'Records/ReturnRecord.class.php';
require_once 'Records/NotifyRecord.class.php';
require_once 'configs/config.php';

class PayResponce extends DBManager{
	private $_statis;
	private $_mongo;
	private $_nType;
	function __construct(){
		$this->_statis = new NotifyRecord();
		
		global $g_arr_db_config;
		$this->connectMySqlPara($g_arr_db_config);
	}
	
	public function recordRequest(){
		$this->_statis->setParam();
		$result = $this->queryOrderid();
		$trade_status = $this->_statis->getTradeStatus();
		if($result){			
			if($trade_status!=$result['0']['trade_status'])
			{
				$this->updateOrderRecord();
			}
		}else{
				$this->insertOrderRecord();
		}
		/**
		 * 以下异步通知改变交易状态，同步不需要处理
		 */
		$real_trade_status = $this->getRealStatus();
		if($real_trade_status!= $trade_status){
			if(!$real_trade_status && ($trade_status == 'TRADE_SUCCESS' || $trade_status == 'TRADE_FINISHED')){
				/**
				 * 这里可以考虑通知支付中心，以POST方式把交易记录告知，同时更改通知状态
				 */
				$result = $this->queryOrderInfo();
				$testData = array(
						'authenid'=>$result[0]['authenid'],
						'platno'   =>$result[0]['platno'],
						'thirdRecordID'=>$result[0]['orderid'],
						'money' =>$result[0]['total_fee'],
						'url'=>array(
								'subject'  => $result[0]['subject'],
								'body'     => $result[0]['body'],
								'show_url' => $result[0]['show_url'],
						),
						'chargeToken'=>$result[0]['charge_token']
				);
				$dataInfo = urlencode(json_encode($testData));
				$count  = 3;
				$isnotify = 0;
				global $g_notify_wall;
				$url = $g_notify_wall;
				while ($count--){
					$status = Fnlib::geturldatapost($url, 'info='.$dataInfo);
					Log::write("PayResponce::queryOrderid():notify status: ".$status, "log");
					if($status == 'success'){
						$isnotify = 1;
						break;
					}
					sleep(10);				
				}
				$this->updatePayTimeAndStatus($isnotify);
			}else{
				/**
				 * 这种状态主要更新TRADE_FINISHED，实际意义不是很大
				 */
				$this->updatePayStatus();
			}
		}
	}
	
	private function queryOrderid(){
		$sql = $this->_statis->getQuerySql();
		$result = $this->executeQuery($sql);
		if($result === false){
			Log::write("PayResponce::queryOrderid():executeQuery() sql: ".$sql." failed", "log");
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
	
	private function updatePayTimeAndStatus($isnotify)
	{
		$sql = $this->_statis->getUpdatePayTimeAndStatusSql($isnotify);
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
	
}