<?php

class NotifyRecord extends Record
{
	public $gmt_create;    //交易创建时间
	public $gmt_payment;   //交易付款时间
	public $gmt_close;     //交易关闭时间
	public $refund_status; //退款状态
	public $gmt_refund;    //退款时间
	public $price;         //商品单价
	public $quantity;      //购买数量
	public $discount;      //折扣
	public $is_total_fee_adjust; //是否调整总价
	public $use_coupon;    //是否使用红包买家;
	public $error_code;    //错误代码;
	public $out_channel_inst; //实际支付渠道;

	public function __construct()
	{
		parent::__construct();
		$this->gmt_create = '';
		$this->exterface  = '';
	}
	
	public function setParam()
	{
		$this->out_trade_no 	= isset($_POST['out_trade_no'])?$_POST['out_trade_no']:'';
		$this->subject			= isset($_POST['subject'])?$_POST['subject']:'';;
		$this->payment_type 	= isset($_POST['payment_type'])?$_POST['payment_type']:'';
		$this->trade_no 		= isset($_POST['trade_no'])?$_POST['trade_no']:'';;
		$this->trade_status 	= isset($_POST['trade_status'])?$_POST['trade_status']:'';
		$this->notify_id 		= isset($_POST['notify_id'])?$_POST['notify_id']:'';
		$this->notify_time 		= isset($_POST['notify_time'])?$_POST['notify_time']:'';
		$this->notify_type 		= isset($_POST['notify_type'])?$_POST['notify_type']:'';
		$this->seller_email 	= isset($_POST['seller_email'])?$_POST['seller_email']:'';
		$this->buyer_email 		= isset($_POST['buyer_email'])?$_POST['buyer_email']:'';
		$this->seller_id 		= isset($_POST['seller_id'])?$_POST['seller_id']:'';
		$this->buyer_id 		= isset($_POST['buyer_id'])?$_POST['buyer_id']:'';
		$this->total_fee		= isset($_POST['total_fee'])?$_POST['total_fee']:0;
		$this->body         	= isset($_POST['body'])?$_POST['body']:'';
		$this->insert_time 		= date('Y-m-d H:i:s');
		$this->bank_seq_no      = isset($_POST['bank_seq_no'])?$_POST['bank_seq_no']:'';
		
		$this->gmt_create 		= isset($_POST['gmt_create'])?$_POST['gmt_create']:'';
		$this->gmt_payment		= isset($_POST['gmt_payment'])?$_POST['gmt_payment']:'';;
		$this->gmt_close 		= isset($_POST['gmt_close'])?$_POST['gmt_close']:'';
	    $this->refund_status    = isset($_POST['refund_status'])?$_POST['refund_status']:'';;
		$this->gmt_refund 		= isset($_POST['gmt_refund'])?$_POST['gmt_refund']:'';
		$this->price 			= isset($_POST['price'])?$_POST['price']:0;
		$this->quantity 		= isset($_POST['quantity'])?$_POST['quantity']:0;
		$this->discount 		= isset($_POST['discount'])?$_POST['discount']:0;
		$this->is_total_fee_adjust 	= isset($_POST['is_total_fee_adjust'])?$_POST['is_total_fee_adjust']:'';
		$this->use_coupon 		= isset($_POST['use_coupon'])?$_POST['use_coupon']:'';
		$this->error_code 		= isset($_POST['error_code'])?$_POST['error_code']:'';
		$this->out_channel_inst = isset($_POST['out_channel_inst'])?$_POST['out_channel_inst']:'';      
	}
	
	public function getInsertSql(){
		$sql = "INSERT INTO alipay_notify (out_trade_no,notify_time,notify_type,notify_id,subject,payment_type,"
				."trade_no,trade_status,gmt_create,gmt_payment,gmt_close,refund_status,gmt_refund,seller_email,"
				."buyer_email,seller_id,buyer_id,price,total_fee,quantity,body,discount,is_total_fee_adjust,"
				."use_coupon,error_code,bank_seq_no,out_channel_inst,insert_time) "
				."VALUES ('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',"
				."'%s','%s','%s','%f','%s','%s','%s','%s','%s','%s')";
	
		$sql = sprintf($sql,$this->out_trade_no, $this->notify_time,$this->notify_type,
						$this->notify_id,$this->subject,$this->payment_type,
				        $this->trade_no,$this->trade_status,$this->gmt_create,
				        $this->gmt_payment,$this->gmt_close,$this->refund_status,
				       $this->gmt_refund,$this->seller_email,$this->buyer_email,
				       $this->seller_id,$this->buyer_id,$this->price,
						$this->total_fee,$this->quantity,$this->body,
				       $this->discount,$this->is_total_fee_adjust,$this->use_coupon,$this->error_code,
				       $this->bank_seq_no,$this->out_channel_inst,$this->insert_time);
		return $sql;
	}
	
	public function getQuerySql()
	{
		$sql = "SELECT trade_status FROM alipay_notify WHERE out_trade_no = '%s'";
		$sql = sprintf($sql,$this->out_trade_no);
		return $sql;		
	}
	
	public function getUpdateSql()
	{
		$sql = "UPDATE alipay_notify SET trade_status = '%s', update_time = '%s',notify_time='%s' WHERE out_trade_no = '%s'";
		$sql = sprintf($sql,$this->trade_status,$this->insert_time,$this->notify_time,$this->out_trade_no);
		return $sql;
	}
	
	public function getTradeStatus()
	{
		return $this->trade_status;
	}
	
	public function getRealTradeStatusSql()
	{
		$sql = "SELECT pay_status FROM recharge_detail WHERE orderid = '%s'";
		$sql = sprintf($sql,$this->out_trade_no);
		return $sql;
	}
	
	public function getUpdatePayTimeAndStatusSql($isnotify)
	{
		$status = $this->getIntStatus();
		$sql = "UPDATE recharge_detail SET pay_status = %d,pay_time = '%s',is_notify=%d WHERE orderid = '%s'";
		$sql = sprintf($sql,$status,$this->notify_time,$isnotify,$this->out_trade_no);
		return $sql;
	}
	
	public function getUpdatePayStatusSql()
	{
		$status = $this->getIntStatus();
		$sql = "UPDATE recharge_detail SET pay_status = %d , update_time = '%s' WHERE orderid = '%s'";
		$sql = sprintf($sql,$status,$this->notify_time,$this->out_trade_no);
		return $sql;
	}
	
	private function getIntStatus()
	{
		switch ($this->trade_status){
			case 'TRADE_SUCCESS':
				$status = 1;
				break;
			case 'TRADE_FINISHED':
				$status = 2;
				break;
			default:
				$status = 0;
				break;
		}
		return  $status;
	}
}