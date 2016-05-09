<?php
class Record
{
	public $out_trade_no;  //商城唯一订单号
	public $subject;       //商品名称
	public $payment_type;  //支付类型
	public $trade_no;      //支付宝交易号
	public $trade_status;  //交易状态:  TRADE_FINISHED普通即时到账的交易成功状态  TRADE_SUCCESS支付成功
	public $notify_id;     //通知校验ID
	public $notify_time;   //通知时间
	public $notify_type;   //通知类型
	public $seller_email;  //卖家支付宝账号
	public $buyer_email;   //买家支付宝账号
	public $seller_id;     //卖家支付宝账户号
	public $buyer_id;      //买家支付宝账户号
	public $total_fee;     //交易金额
	public $body;          //商品描述
	public $bank_seq_no;   //网银流水

	
	
	public function __construct()
	{
		$this->out_trade_no 	= '';
		$this->subject		= '';
		$this->payment_type 	= '';
		$this->trade_no 	= '';
		$this->trade_status 	= '';
		$this->notify_id 	= '';
		$this->notify_time 	= '';
		$this->notify_type = '';
		$this->seller_email 	= '';
		$this->buyer_email 	= '';
		$this->seller_id 		= '';
		$this->buyer_id 		= '';
		$this->total_fee	= 0;
		$this->body         = '';
		$this->insert_time = date('Y-m-d H:i:s');
		$this->bank_seq_no  = '';
	}
	
	public function getQueryOrderInfoSql()
	{
		$sql = "SELECT total_fee,subject,orderid,body,show_url,return_url,authenid,charge_token,platno FROM recharge_detail WHERE orderid= '%s'";
		$sql = sprintf($sql,$this->out_trade_no);
		return $sql;
	}
}