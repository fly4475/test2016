<?php
require_once 'lib/DBManager.lib.php';
require_once 'lib/WriteLog.lib.php';
require_once 'Records/ReturnRecord.class.php';
require_once 'Records/NotifyRecord.class.php';
require_once 'configs/config.php';

class PayResponce extends DBManager{
	private $_statis;
	private $_mongo;
	private $_nType;
	function __construct($nType){
		$this->_nType = $nType;
		if($nType == 0){
			$this->_statis = new ReturnRecord();
		}else{
			$this->_statis = new NotifyRecord();
		}
		
		
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
		 * 以下异步通知改变交易状态
		 */
		if($this->_nType == 1){
			$real_trade_status = $this->getRealStatus();			
			if($real_trade_status!=false && $real_trade_status['pay_status']!= $trade_status){
				if($real_trade_status['pay_status'] === false && ($trade_status == 'TRADE_SUCCESS' || $trade_status == 'TRADE_FINISHED')){
					$this->updatePayTimeAndStatus($real_trade_status);
					/**
					 * 这里可以考虑通知支付中心，以POST方式把交易记录告知
					 */
				}else{
					/**
					 * 这种状态主要更新TRADE_FINISHED，实际意义不是很大
					 */
					$this->updatePayStatus();
				}
			}
		}else{
			$result = $this->queryOrderInfo();
			$return_url = $result[0]['return_url'];
			Log::write("PayResponce::recordRequest():window.location return_url: ".$return_url, "log");
			echo "<script language='javascript'>window.location='".$return_url."'</script>";
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
				$result[0]['pay_status'] = 'TRADE_SUCCESS';
				break;
			case '2':
				$result[0]['pay_status'] = 'TRADE_FINISHED';
				break;
			default:
				$result[0]['pay_status'] = false;
				break;
		}
		return $result[0];
	}
	
	private function updatePayTimeAndStatus($real_trade_status)
	{
		$sql = $this->_statis->getUpdatePayTimeAndStatusSql();
		$result = $this->executeSql($sql);
		if(!$result){
			Log::write("PayResponce::updatePayTimeAndStatus() getUpdatePayTimeAndStatusSql :executeSql() sql: ".$sql." failed", "log");
			return false;
		}
		/**
		 * 修改优惠券已使用的逻辑
		 */
		if($real_trade_status['is_used_coupon'] == 1){
			$sql = $this->_statis->getUpdateCouponStatusSql($real_trade_status);
			$result = $this->executeSql($sql);
			if(!$result){
				Log::write("PayResponce::updatePayTimeAndStatus() getUpdateCouponStatusSql:executeSql() sql: ".$sql." failed", "log");
				return false;
			}
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