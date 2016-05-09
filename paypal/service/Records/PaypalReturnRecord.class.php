<?php
require_once 'PaypalRecord.class.php';
require_once dirname(__FILE__).'/../configs/config.php';
class ReturnRecord extends Record
{
	public $is_success;  //接口调用是否成功
	public $exterface;   //接口名称
    public $trade_type; //交易类型
    public  $is_paypal; //是否贝宝支付
    public  $order_time; //付款时间
    public  $pending_reason; //付款状态为pending时，正在审核的原因
    public  $reason_code; //交易状态为撤销时，撤销的原因
	
	
	public function __construct()
	{	
		parent::__construct();
		$this->is_success = '';
		$this->exterface  = '';
        $this->trade_type = '';
        $this->is_paypal = '';
        $this->order_time = '';
        $this->pending_reason = '';
        $this->reason_code = '';
	}
	
	public function setParamDetail($token)
	{
		/*
		$this->out_trade_no 	= isset($_SESSION['invnm'])?$_SESSION['invnm']:'';
		$this->subject			= isset($_SESSION['subject'])?$_SESSION['subject']:'';
        $this->total_fee		= isset($_SESSION['TotalAmount'])?$_SESSION['TotalAmount']:0;
		$this->buyer_email 	= isset($_SESSION['email'])?$_SESSION['email']:'';
        $this->bank_seq_no    = isset($_SESSION['bank_seq_no'])?$_SESSION['bank_seq_no']:'';
        $this->seller_id 	    = isset($_SESSION['sellerId'])?$_SESSION['sellerId']:'';
        $this->body         	= isset($_SESSION['desc'])?$_SESSION['desc']:'';
        $this->notify_time    = isset($_SESSION['TIMESTAMP'])?$_SESSION['TIMESTAMP']:'';  */
        global $g_arr_redis_config;
        $redis = new Redis();
        $redis->connect($g_arr_redis_config['host'], $g_arr_redis_config['port']);
        $redis->auth($g_arr_redis_config['pwd']);
        $redisstr = json_decode($redis->get($token),true);
        $this->out_trade_no 	= isset($redisstr['invnm'])?$redisstr['invnm']:'';
        $this->subject			= isset($redisstr['subject'])?$redisstr['subject']:'';
        $this->total_fee		= isset($redisstr['TotalAmount'])?$redisstr['TotalAmount']:0;
        $this->buyer_email 	= isset($redisstr['buyeremail'])?$redisstr['buyeremail']:'';
        $this->bank_seq_no    = $token;
        $this->seller_id 	    = isset($redisstr['sellerId'])?$redisstr['sellerId']:'';
        $this->body         	= isset($redisstr['desc'])?$redisstr['desc']:'';
        $this->notify_time    = isset($redisstr['TIMESTAMP'])?$redisstr['TIMESTAMP']:'';

        $this->insert_time 	= date('Y-m-d H:i:s');
        $this->payment_type 	= '4';
        $redis->close();
    }

    public  function  setParamDoCheckout($token)
    {
        global $g_arr_redis_config;
        $redis = new Redis();
        $redis->connect($g_arr_redis_config['host'], $g_arr_redis_config['port']);
        $redis->auth($g_arr_redis_config['pwd']);
        $redisstr = json_decode($redis->get($token),true);

		//$this->seller_id 		= isset($_SESSION['paymentrequest_0_sellerpaypalaccountid'])?$_SESSION['paymentrequest_0_sellerpaypalaccountid']:'';
		$this->buyer_id 		= isset($redisstr['payer_id'])?$redisstr['payer_id']:'';
		//$this->total_fee		= isset($_GET['paymentinfo_0_amt'])?$_GET['paymentinfo_0_amt']:0;
        $this->total_fee		= isset($redisstr['TotalAmount'])?$redisstr['TotalAmount']:0;
        $this->payment_type 	= '4';
        $this->notify_time 	= isset($redisstr['TIMESTAMP'])?$redisstr['TIMESTAMP']:'';
        $this->trade_no 		= isset($redisstr['paymentinfo_0_transactionid'])?$redisstr['paymentinfo_0_transactionid']:'';
        $this->trade_type       = isset($redisstr['paymentinfo_0_transactiontype'])?$redisstr['paymentinfo_0_transactiontype']:'';
        $this->trade_status 	= isset($redisstr['paymentinfo_0_paymentstatus'])?$redisstr['paymentinfo_0_paymentstatus']:'';
       // $this->total_fee		= isset($_GET['paymentinfo_0_amt'])?$_GET['paymentinfo_0_amt']:0;
        $this->exterface        = isset($redisstr['method'])?$redisstr['method']:'';
        $this->order_time       = isset($redisstr['paymentinfo_0_ordertime'])?$redisstr['paymentinfo_0_ordertime']:'';
        $this->pending_reason   = isset($redisstr['paymentinfo_0_pendingreason'])?$redisstr['paymentinfo_0_pendingreason']:'';
        $this->reason_code      = isset($redisstr['paymentinfo_0_reasoncode'])?$redisstr['paymentinfo_0_reasoncode']:'';
        $this->bank_seq_no        = $token;
        $redis->close();
    }

    public  function  setSuccess()
    {
        $this->is_success  =  '1';
    }

    public  function  setFail()
    {
        $this->is_success  =  '0';
    }

    public function getInsertDetailSql()
    {
        Log::write("PaypalReturnRecord:getInsertDetailSql()".$this->notify_time,"log");
        $sql = "INSERT INTO alipay_return (out_trade_no,payment_type,is_success,subject,total_fee,seller_id,buyer_email,body,bank_seq_no,notify_time,insert_time)"
            ."VALUES('%s','%s','%s','%s','%f','%s','%s','%s','%s','%s','%s');";
        Log::write("PaypalReturnRecord:getInsertSql() out_trade_no".$this->out_trade_no,"log");
        $sql = sprintf($sql,$this->out_trade_no,$this->payment_type, $this->is_success,$this->subject,$this->total_fee,$this->seller_id,
            $this->buyer_email,$this->body,$this->bank_seq_no,$this->notify_time,$this->insert_time);
        Log::write("PaypalReturnRecord:getInsertDetailSql() sql".$sql,"log");
        return $sql;
    }

    public function  getDetailUpdateSql()
    {
        Log::write("PaypalReturnRecord:getDetailUpdateSql()","log");
        $sql = "UPDATE alipay_return  SET payment_type='%s',is_success='%s',exterface='%s',trade_no='%s',trade_status='%s',buyer_id='%s',total_fee='%f',"
            ."trade_type='%s',order_time='%s',pending_reason='%s',reason_code='%s',notify_time='%s',insert_time='%s' WHERE bank_seq_no='%s';";
        Log::write("PaypalReturnRecord:getInsertSql() out_trade_no".$this->out_trade_no,"log");
        $sql = sprintf($sql,$this->payment_type,$this->is_success,$this->exterface,$this->trade_no,
            $this->trade_status,$this->buyer_id,$this->total_fee,$this->trade_type,
            $this->order_time,$this->pending_reason,$this->reason_code,
            $this->notify_time,$this->insert_time,$this->bank_seq_no);
        Log::write("PaypalReturnRecord:getDetailUpdateSql() sql".$sql,"log");
        return $sql;
    }

	public function getInsertSql()
    {
        Log::write("PaypalReturnRecord:getInsertSql()","log");
		$sql = "INSERT INTO alipay_return (out_trade_no,is_success,subject,payment_type,exterface,trade_no,"
				."trade_status,buyer_email,seller_id,buyer_id,total_fee,body,trade_type,"
				."order_time,pending_reason,reason_code,insert_time)"
                 ."VALUES('%s','%s','%s','%s','%s','%s','%s','%s','%s',"
                 ."'%s','%f','%s','%s','%s','%s','%s','%s','%s');";
        Log::write("PaypalReturnRecord:getInsertSql() out_trade_no".$this->out_trade_no,"log");
		$sql = sprintf($sql,$this->out_trade_no, $this->is_success,$this->subject,
							$this->payment_type,$this->exterface,$this->trade_no,
							$this->trade_status,$this->buyer_email,$this->seller_id,
							$this->buyer_id,$this->total_fee,$this->body,$this->trade_type,
                            $this->order_time,$this->pending_reason,$this->reason_code,
							$this->insert_time);
        Log::write("PaypalReturnRecord:getInsertSql() sql".$sql,"log");
        return $sql;
	}
	
	public function getQuerySql()
	{
		$sql = "SELECT trade_status FROM alipay_return WHERE out_trade_no = '%s'";
		$sql = sprintf($sql,$this->out_trade_no);
        Log::write("PaypalReturnRecord:getQuerySql() sql".$sql,"log");
		return $sql;
	}

    public function getQueryDetailSql()
    {
        $sql = "SELECT * FROM alipay_return WHERE bank_seq_no = '%s'";
        $sql = sprintf($sql,$this->bank_seq_no);
        Log::write("PaypalReturnRecord:getQueryDetailSql() sql".$sql,"log");
        return $sql;
    }

	
	public function getUpdateSql()
	{
		$sql = "UPDATE alipay_return SET trade_status = '%s', update_time = '%s' WHERE out_trade_no = '%s'";
		$sql = sprintf($sql,$this->trade_status,$this->notify_time,$this->out_trade_no);
		return $sql;
	}
	
	public function getTradeStatus()
	{
		return $this->trade_status;
	}
}