<?php
require_once 'Record.class.php';
class ReturnRecord extends Record
{
	public $is_success;  //接口调用是否成功
	public $exterface;   //接口名称

	
	
	public function __construct()
	{	
		parent::__construct();
		$this->is_success = '';
		$this->exterface  = '';
	}
	
	public function setParam()
	{
		$this->out_trade_no 	= isset($_GET['out_trade_no'])?$_GET['out_trade_no']:'';
		$this->subject			= isset($_GET['subject'])?$_GET['subject']:'';;
		$this->payment_type 	= isset($_GET['payment_type'])?$_GET['payment_type']:'';
		$this->trade_no 		= isset($_GET['trade_no'])?$_GET['trade_no']:'';;
		$this->trade_status 	= isset($_GET['trade_status'])?$_GET['trade_status']:'';
		$this->notify_id 		= isset($_GET['notify_id'])?$_GET['notify_id']:'';
		$this->notify_time 		= isset($_GET['notify_time'])?$_GET['notify_time']:'';
		$this->notify_type 		= isset($_GET['notify_type'])?$_GET['notify_type']:'';
		$this->seller_email 	= isset($_GET['seller_email'])?$_GET['seller_email']:'';
		$this->buyer_email 		= isset($_GET['buyer_email'])?$_GET['buyer_email']:'';
		$this->seller_id 		= isset($_GET['seller_id'])?$_GET['seller_id']:'';
		$this->buyer_id 		= isset($_GET['buyer_id'])?$_GET['buyer_id']:'';
		$this->total_fee		= isset($_GET['total_fee'])?$_GET['total_fee']:0;
		$this->body         	= isset($_GET['body'])?$_GET['body']:'';
		$this->insert_time 		= date('Y-m-d H:i:s');
		$this->is_success       = isset($_GET['is_success'])?$_GET['is_success']:'';
		$this->exterface        = isset($_GET['service'])?$_GET['service']:'';//pc端为exterface
		$this->bank_seq_no      = isset($_GET['bank_seq_no'])?$_GET['bank_seq_no']:'';
	}
	
	public function getInsertSql(){
		$sql = "INSERT INTO alipay_return (out_trade_no,is_success,subject,payment_type,exterface,trade_no,"
				."trade_status,notify_id,notify_time,notify_type,seller_email,buyer_email,seller_id,buyer_id,"
				."total_fee,body,bank_seq_no,insert_time)"
                 ."VALUES('%s','%s','%s','%s','%s','%s','%s','%s','%s',"
                 ."'%s','%s','%s','%s','%s','%f','%s','%s','%s');";
		
		$sql = sprintf($sql,$this->out_trade_no, $this->is_success,$this->subject,
							$this->payment_type,$this->exterface,$this->trade_no,
							$this->trade_status,$this->notify_id,$this->notify_time,$this->notify_type,
							$this->seller_email,$this->buyer_email,$this->seller_id,
							$this->buyer_id,$this->total_fee,$this->body,$this->bank_seq_no,
							$this->insert_time);
		return $sql;
	}
	
	public function getQuerySql()
	{
		$sql = "SELECT trade_status FROM alipay_return WHERE out_trade_no = '%s'";
		$sql = sprintf($sql,$this->out_trade_no);
		return $sql;
	}
	
	public function getUpdateSql()
	{
		$sql = "UPDATE alipay_return SET trade_status = '%s', update_time = '%s',notify_time='%s' WHERE out_trade_no = '%s'";
		$sql = sprintf($sql,$this->trade_status,$this->insert_time,$this->notify_time,$this->out_trade_no);
		return $sql;
	}
	
	public function getTradeStatus()
	{
		return $this->trade_status;
	}
}