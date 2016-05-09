<?php
//require_once '../lib/WriteLog.lib.php';

class Order
{
    public $orderid;  //商城唯一订单号
    public $payment_status; //交易状态:  TRADE_FINISHED普通即时到账的交易成功状态  TRADE_SUCCESS支付成功
    public $subject;       //商品名称
    public $platno;  //支付类型
    public $name;    //用户名
    public $pay_time;   //支付时间
    public $return_url;
    public $show_url;     //卖家paypal账户号
    public $charge_token;      //买家paypal账户号
    public $total_fee;     //交易金额
    public $body;          //商品描述
    public $insert_time;   //插入时间
    public $update_time;  //更新时间



    public function __construct()
    {
        $this->orderid 	= '';
        $this->subject		= '';
        $this->payment_status 	= '';
        $this->platno 	= '';
        $this->name 	= '';
        $this->pay_time 	= '';
        $this->return_url 	= '';
        $this->show_url 	= '';
        $this->charge_token 		= '';
        $this->total_fee	= 0;
        $this->body         = '';
        $this->insert_time = date('Y-m-d H:i:s');
        $this->update_time  = date('Y-m-d H:i:s');;
    }

    public function setParam($info)
    {
        $this->orderid 	= isset($info['WIDout_trade_no'])?$info['WIDout_trade_no']:'';
        $this->subject			= isset($info['WIDsubject'])?$info['WIDsubject']:'';
        $this->payment_status = '0';
        $this->platno          = '4';
        $this->total_fee		= isset($info['WIDtotal_fee'])?$info['WIDtotal_fee']:0;
        $this->return_url 	= isset($info['return_url'])?$info['return_url']:'';
        $this->show_url    = isset($info['WIDshow_url'])?$info['WIDshow_url']:'';
        $this->name 	    = isset($info['WIDextern_token'])?$info['WIDextern_token']:'';
        $this->body         	= isset($info['WIDbody'])?$info['WIDbody']:'';
        $this->insert_time 	= date('Y-m-d H:i:s');

        //Log::write("ReviewOrder::setParam: ".$this->orderid."  ".$this->subject."  ".$this->total_fee."  ". $this->return_url."  ".$this->show_url
         //   ."  ".$this->name."  ".$this->body, "log");
    }
    public function  getInsertSql() {
       // Log::write("PayResponce::insertOrderInfo()", "log");
        $sql = "INSERT INTO recharge_detail (orderid,pay_status,platno,name,total_fee,return_url,subject,body,show_url)"
            ."VALUES('%s','%s','%s','%s','%f','%s','%s','%s','%s');";
        $sql = sprintf($sql,$this->orderid, $this->payment_status,$this->platno,$this->name,$this->total_fee,$this->return_url,$this->subject,
            $this->body,$this->show_url);

        //Log::write("Order sql::getInsertSql()  ".$sql, "log");
        return $sql;
    }

}