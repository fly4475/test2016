<?php
class PayController extends Controller
{	
	/**
	 * 充值接口
	 */
	public function actionRecharge()
	{
		$amount 		= isset($_REQUEST['amount'])?$_REQUEST['amount']:'0.01';//金额
		$mobile 		= isset($_REQUEST['mobile'])?$_REQUEST['mobile']:'22222222';//账号
		$returnUrl 		= isset($_REQUEST['returnUrl'])?$_REQUEST['returnUrl']:'baidu.com';//支付成功返回url
		$authenid   	= isset($_REQUEST['authenid'])?$_REQUEST['authenid']:'';//代理平台id
		$platno   		= isset($_REQUEST['platno'])?$_REQUEST['platno']:'';//1支付宝手机，2为支付宝PC，3为贝宝
		$chargeToken	= isset($_REQUEST['chargeToken'])?$_REQUEST['chargeToken']:'';
		$show_url       = isset($_REQUEST['show_url'])?$_REQUEST['show_url']:'';
		if($amount=='' || $returnUrl=='' || $chargeToken==''){
			$json_result = Fnlib::get_rsp_result(false,'20003','Parameter missing!');
			exit($json_result);
		}
		if(!((int)$platno>0&&(int)$platno<4)){
			$json_result = Fnlib::get_rsp_result(false,'20003','Parameter platno error!');
			exit($json_result);
		}
		/**
		 * 获取订单号
		 */
		$dataArr   = array(
				'authenid' => $authenid,
				'platno'   => $platno,
				'name'     => $mobile,
				'order_info' => array(
						'subject'  		=> '订单名称：图灵猫用户充值'.$amount.'元',
						'total_fee'		=>$amount,
						'body'     		=> '图灵猫用户于'.date('Y-m-d H:i:s').'充值'.$amount.'元',
						'return_url'  	=> $returnUrl,
						'orderid' 		=> substr(md5($mobile.date('YmdHis').$this->num_rand()), 0,32),
						'show_url'      => $show_url,
				),
				'token'   => $chargeToken,	
		);
		$apiModel   = new ModelThirdInterface();
		$result = $apiModel->saveRecharge($dataArr);
		/**
		 * 开始支付
		 */
		if($result){
			$apiModel->jumpPayment($dataArr['order_info'],$platno);
		}else{
			$json_result = Fnlib::get_rsp_result(false,'40005','Data processing exception!');
			exit($json_result);
		}

	}
	
	
	/**
	 * 新官网商城用户支付
	 */
	public function actionSpend()
	{
		$strOrderInfo = Fnlib::declassify($_GET['info']);
		$objOrderInfo = json_decode($strOrderInfo,true);
		$order_info = array(
				'subject'  		=> '商品名称：'.$objOrderInfo['subject'],
				'total_fee'		=> $objOrderInfo['total_fee'],
				'body'     		=> '商品body',
				'return_url'  	=> $objOrderInfo['show_url'],
				'orderid' 		=> $objOrderInfo['orderid'],
				'show_url'      => $objOrderInfo['show_url'],
		);
		$apiModel   = new ModelThirdInterface();
		$apiModel->jumpPayment($order_info,4);
	}
	
	
	
	
	
	
	
	

	
	
	
	
	
	
	
	
	
	
	
	
	
	/**
	 * 消费接口
	 */
	public function actionConsume()
	{
		$url = 'http://172.16.45.45:18888/api/consume';
//		$url = 'http://localhost/cj2013/postAndGet.php';
		$testData = array(	'account'=>'test001',
						  	'passwd' =>'pswd001',
						 	'authenid'=>'1',
							'busynessnum'=>'aixiba',
							'orderid'=>'TRC20150602000008',
							'feetype'=>'2',
							'money'=>'0.01',
							'srcTerminal'=>'1',
							'orderDesc'=>'商品描述图灵猫',
							'url'=>array(
									'subject'  => '订单名称图灵猫',
									'show_url' => 'http://www.baidu.com'
							),
						);
		$testData = json_encode($testData).PHP_EOL;
		$myinfo = Fnlib::geturldatapost($url, 'info='.$testData);
		echo $myinfo;
	}
	
	
	public function actionAuthen()
	{
		$url = 'http://172.16.45.45:18888/api/authen';
		//		$url = 'http://localhost/cj2013/postAndGet.php';
		$testData = array(	'account'=>'test001',
		'passwd' =>'pswd001',
		'authenid'=>'1',
		);
		$testData = json_encode($testData).PHP_EOL;
		$myinfo = Fnlib::geturldatapost($url, 'info='.$testData);
		echo $myinfo;
	}	
	
	/**
	 * 确认收货接口
	 */
	public function actionPostconsume()
	{
		$url = 'http://112.74.132.136:18888/api/postconsume';//测试接口
//		$url = 'http://120.24.81.23:18888/api/postconsume';//正式接口
//		$url = 'http://localhost/cj2013/postAndGet.php';
		$testData = array(	'token'=>'13266775550',
							'authenid'=>'1',
							'busynessnum'=>'wash8001',
							'orderid'=>'TRC20150602000008',
							'acktype'=>'1',//1为取消，9为确认
							'money'=>'0.01',
							'ackreason'=>0,
							'reasondesc'=>'',
							'url'=>array(
									'subject'  => '订单名称图灵猫',
									'body'     => '商品描述图灵猫',
									'show_url' => 'http://www.baidu.com'
							),
							'source'=>1//0为中控，1为PC
		);
		$testData = json_encode($testData).PHP_EOL;
		$myinfo = Fnlib::geturldatapost($url, 'info='.$testData);
		echo $myinfo;
	}
	
	public function num_rand(){
		$lenth = 6;
		$randval   = '';
		for($i=0;$i<$lenth;$i++){
			$randval.= mt_rand(0,9);
		}
		return $randval;
	}
}