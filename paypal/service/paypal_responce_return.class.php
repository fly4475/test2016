<?php
require_once 'lib/DBManager.lib.php';
require_once 'lib/WriteLog.lib.php';
require_once 'Records/PaypalReturnRecord.class.php';
require_once 'configs/config.php';

class PayResponce extends DBManager{
	private $_statis;
	private $_mongo;
	function __construct(){

		$this->_statis = new ReturnRecord();
	
		global $g_arr_db_config;
		$this->connectMySqlPara($g_arr_db_config);
	}

    public function  recordDetailRequest($token){
        Log::write("paypal_response_return::recordDetailRequest  ", "log");
        $this->_statis->setParamDetail($token);
        $this->_statis->setFail();
        $result = $this->queryDetailRecord();
        if($result){
            //已经插入过Detail数据
        }else{
            $this->insertDetailRecord();
        }

    }
	
	public function recordRequest($token){
        Log::write("paypal_response_return::recordRequest  ", "log");
		$this->_statis->setParamDoCheckout($token);
        $this->_statis->setSuccess();
        Log::write("paypal_response_return::recordRequest  invnm", "log");
		$result = $this->queryDetailRecord();
		$trade_status = $this->_statis->getTradeStatus();
        Log::write("paypal_response_return  result num： ".!$result, "log");

		if($result){
                Log::write("paypal_response_return::recordRequest  updateRecord", "log");
				$this->updateDetailRecord();
		}else{
                Log::write("paypal_response_return::recordRequest  insertRecord", "log");
				//出错
		}
		/**
		 * 以下跳转到第三方支付成功的页面，进行相应的逻辑处理
		 */
		//交易状态
		if($trade_status == 'Processed' ||$trade_status == 'Completed') {
			//判断该笔订单是否在商户网站中已经做过处理
			//如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
			//如果有做过处理，不执行商户的业务程序
			/**
			 * 支付成功，域名重定向
			 */
            Log::write("PaypalReturnRecord:trade_status2   ".$trade_status ,"log");
			//$result = $this->queryOrderInfo();
            global $g_arr_redis_config;
            $redis = new Redis();
            $redis->connect($g_arr_redis_config['host'], $g_arr_redis_config['port']);
            $redis->auth($g_arr_redis_config['pwd']);
            $redisstr = json_decode($redis->get($token),true);
            if (!$redisstr) {
                Log::write("paypal_response_return:  result null","log");
            }
			$return_url = $redisstr['returnurl'];
            Log::write("paypal_response_return::recordRequest return url ".$return_url, "log");
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
        Log::write("PayResponce::queryOrderid():111", "log");
		$sql = $this->_statis->getQueryOrderInfoSql();
		$result = $this->executeQuery($sql);
		if($result === false){
			Log::write("PayResponce::queryOrderid():executeQuery() sql: ".$sql." failed", "log");
			return false;
		}
		return $result;
	}
	
	private function queryOrderid()
    {
		$sql = $this->_statis->getQuerySql();
		$result = $this->executeQuery($sql);
		if($result === false){
//			Log::write("PayResponce::queryOrderid():executeQuery() sql: ".$sql." failed", "log");
			return false;
		}
		return $result;
	}

    private function  queryDetailRecord()
    {
        $sql = $this->_statis->getQueryDetailSql();
        $result = $this->executeQuery($sql);
        if($result === false){
//			Log::write("PayResponce::queryOrderid():executeQuery() sql: ".$sql." failed", "log");
            return false;
        }
        return $result;
    }
	
	private function insertOrderRecord()
	{
        Log::write("PayResponce::insertOrderRecord():executeSql()","log");
		$sql = $this->_statis->getInsertSql();
		$result = $this->executeSql($sql);
		if(!$result){
			Log::write("PayResponce::insertOrderRecord():executeSql() sql: ".$sql." failed", "log");
			return false;
		}
		return true;
	}

    private function  insertDetailRecord()
    {
        Log::write("PayResponce::insertDetailRecord():executeSql()","log");
        $sql = $this->_statis->getInsertDetailSql();
        $result = $this->executeSql($sql);
        if(!$result){
            Log::write("PayResponce::insertDetailRecord():executeSql() sql: ".$sql." failed", "log");
            return false;
        }
        return true;
    }
	
	private function updateOrderRecord()
	{
        Log::write("PayResponce::updateOrderRecord():executeSql()","log");
		$sql = $this->_statis->getUpdateSql();
		$result = $this->executeSql($sql);
		if(!$result){
			Log::write("PayResponce::updateOrderRecord():executeSql() sql: ".$sql." failed", "log");
			return false;
		}
		return true;
	}

	private function updateDetailRecord()
    {
        Log::write("PayResponce::updateDetailRecord():executeSql()","log");
        $sql = $this->_statis->getDetailUpdateSql();
        $result = $this->executeSql($sql);
        if(!$result){
            Log::write("PayResponce::updateDetailRecord():executeSql() sql: ".$sql." failed", "log");
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