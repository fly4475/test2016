<?php
require_once 'PaypalRecord.class.php';
require_once dirname(__FILE__).'/../lib/WriteLog.lib.php';

class NotifyRecord extends Record
{
	public $gmt_create;    //交易创建时间
	public $gmt_payment;   //交易付款时间
//	public $gmt_close;     //交易关闭时间
//	public $refund_status; //退款状态
//	public $gmt_refund;    //退款时间
//	public $price;         //商品单价
	public $quantity;      //购买数量
//	public $discount;      //折扣
//	public $is_total_fee_adjust; //是否调整总价
//	public $use_coupon;    //是否使用红包买家;
	public $error_code;    //错误代码;
	public $out_channel_inst; //实际支付渠道;
    public $pending_reason; //付款正在审核的原因
    public $mc_current; //付款币种
    public  $notify_id; //通知校验id
    public  $price;//商品单价

	public function __construct()
	{
		parent::__construct();
		$this->gmt_create = '';
		$this->gmt_payment  = '';
        $this->quantity = '';
        $this->error_code = '';
        //$this->out_channel_inst = '';
        $this->pending_reason = '';
        $this->mc_current = '';
        $this->notify_id ='';
        $this->price='';
	}
	
	public function setParam()
	{
          Log::write("PalpayNotifyRecord:: setParam ".json_encode($_POST), "log");
      //   Log::write("PalpayNotifyRecord:: setParam ".json_encode($_GET), "log");
          $this->out_trade_no 	= isset($_POST['invoice'])?$_POST['invoice']:'';
           $this->subject			= isset($_POST['item_name'])?$_POST['item_name']:'';;
           $this->payment_type 	= '4';
           $this->trade_no 		= isset($_POST['txn_id'])?$_POST['txn_id']:'';
           $this->trade_status 	= isset($_POST['payment_status'])?$_POST['payment_status']:'';
           $this->gmt_payment  	= isset($_POST['payment_date'])?$_POST['payment_date']:'';
           $this->seller_email 	= isset($_POST['receiver_email'])?$_POST['receiver_email']:'';
           $this->buyer_email  	= isset($_POST['payer_email'])?$_POST['payer_email']:'';
           $this->seller_id 		= isset($_POST['receiver_id'])?$_POST['receiver_id']:'';
           $this->buyer_id 		= isset($_POST['payer_id'])?$_POST['payer_id']:'';
           $this->total_fee		= isset($_POST['mc_gross'])?$_POST['mc_gross']:0;
           $this->insert_time    = date('Y-m-d H:i:s');
           $this->update_time    = date('Y-m-d H:i:s');
           //$this->gmt_create 		= isset($_POST['payment_date'])?$_POST['payment_date']:'';
           $this->quantity 		= isset($_POST['quantity'])?$_POST['quantity']:0;
           $this->error_code 		= isset($_POST['error_code'])?$_POST['error_code']:'';
           $this->pending_reason = isset($_POST['pending_reason'])?$_POST['pending_reason']:'';
           $this->mc_current     = isset($_POST['mc_currency'])?$_POST['mc_currency']:'';
           $this->notify_id       = isset($_POST['verify_sign'])?$_POST['verify_sign']:'';
           $this->price            = $this->quantity?$this->total_fee/$this->quantity:0;

      /*         $this->out_trade_no 	= isset($_GET['invoice'])?$_GET['invoice']:'';
           $this->subject			= isset($_GET['item_name'])?$_GET['item_name']:'';;
           $this->payment_type 	= '4';
           $this->trade_no 		= isset($_GET['txn_id'])?$_GET['txn_id']:'';
           $this->trade_status 	= isset($_GET['payment_status'])?$_GET['payment_status']:'';
           $this->gmt_payment 	    = isset($_GET['payment_date'])?$_GET['payment_date']:'';
           $this->seller_email 	= isset($_GET['receiver_email'])?$_GET['receiver_email']:'';
           $this->buyer_email    	= isset($_GET['payer_email'])?$_GET['payer_email']:'';
           $this->seller_id 		= isset($_GET['receiver_id'])?$_GET['receiver_id']:'';
           $this->buyer_id 		= isset($_GET['payer_id'])?$_GET['payer_id']:'';
           $this->total_fee		= isset($_GET['mc_gross'])?$_GET['mc_gross']:0;
           $this->insert_time    = date('Y-m-d H:i:s');
           $this->update_time    = date('Y-m-d H:i:s');
          // $this->gmt_create 		= isset($_GET['payment_date'])?$_GET['payment_date']:'';
           $this->quantity 		= isset($_GET['quantity'])?$_GET['quantity']:0;
           $this->error_code 		= isset($_GET['error_code'])?$_GET['error_code']:'';
           $this->pending_reason = isset($_GET['pending_reason'])?$_GET['pending_reason']:'';
           $this->mc_current     = isset($_GET['mc_currency'])?$_GET['mc_currency']:'';
        $this->notify_id       = isset($_GET['verify_sign'])?$_GET['verify_sign']:'';  */
        Log::write("PalpayNotifyRecord:: setParam  End ", "log");
	}
	
	public function getInsertSql(){
		$sql = "INSERT INTO alipay_notify (notify_type,price,notify_id,out_trade_no,subject,payment_type,trade_no,trade_status,gmt_payment,seller_email,"
				."buyer_email,seller_id,buyer_id,total_fee,quantity,error_code,pending_reason,mc_current,insert_time) "
				."VALUES ('%s','%f','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%f','%s','%s','%s','%s','%s')";
	
		$sql = sprintf($sql,'trade_status_sync',$this->price,$this->notify_id,$this->out_trade_no,$this->subject,$this->payment_type,
				        $this->trade_no,$this->trade_status,date('Y-m-d H:m:s',strtotime($this->gmt_payment)),
				        $this->seller_email,$this->buyer_email,$this->seller_id
                        ,$this->buyer_id,$this->total_fee,$this->quantity,$this->error_code,
				       $this->pending_reason,$this->mc_current,$this->insert_time);
        Log::write("PalpayNotifyRecord:: getInsertSql gmt_payment ".$this->gmt_payment, "log");
		return $sql;
	}
	
	public function getQuerySql()
	{
		$sql = "SELECT trade_status FROM alipay_notify WHERE out_trade_no = '%s'";
		$sql = sprintf($sql,$this->out_trade_no);
		return $sql;		
	}

    public function updateOrderDetail()
    {
        $sql = "UPDATE recharge_detail SET pay_status='1', is_notify='1',pay_time='%s',update_time='%s' WHERE orderid = '%s'";
        $sql = sprintf($sql,date('Y-m-d H:m:s',strtotime($this->gmt_payment)),$this->update_time,$this->out_trade_no);
        Log::write("PalpayNotifyRecord:: updateOrderDetail ".$sql, "log");
        return $sql;
    }
	
	public function getUpdateSql()
	{
		$sql = "UPDATE alipay_notify SET trade_status = '%s', update_time = '%s' WHERE out_trade_no = '%s'";
		$sql = sprintf($sql,$this->trade_status,$this->update_time,$this->out_trade_no);
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
			case 'PROCESSED':
				$status = 1;
				break;
			case 'COMPLETED':
				$status = 2;
				break;
			default:
				$status = 0;
				break;
		}
		return  $status;
	}
}