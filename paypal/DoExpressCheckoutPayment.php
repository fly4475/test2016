<?php
session_start();
/**********************************************************
DoExpressCheckoutPayment.php

This functionality is called to complete the payment with
PayPal and display the result to the buyer.

The code constructs and sends the DoExpressCheckoutPayment
request string to the PayPal server.

Called by GetExpressCheckoutDetails.php.

Calls CallerService.php and APIError.php.

**********************************************************/

require_once 'CallerService.php';
require_once 'service/lib/WriteLog.lib.php';
require_once 'service/configs/config.php';




ini_set('session.bug_compat_42',0);
ini_set('session.bug_compat_warn',0);

/* Gather the information to make the final call to
   finalize the PayPal payment.  The variable nvpstr
   holds the name value pairs
   */
/*
$token =urlencode( $_SESSION['token']);
$paymentAmount =urlencode ($_SESSION['TotalAmount']);
$paymentType = urlencode($_SESSION['paymentType']);
$currCodeType = urlencode($_SESSION['currCodeType']);
$payerID = urlencode($_SESSION['payer_id']); */
$serverName = urlencode($_SERVER['SERVER_NAME']);

$token = $_POST['token'];
global $g_arr_redis_config;
$redis = new Redis();
$redis->connect($g_arr_redis_config['host'], $g_arr_redis_config['port']);
$redis->auth($g_arr_redis_config['pwd']);
$redisstr = json_decode($redis->get($token),true);
//var_dump($redisstr);exit;
$paymentAmount = $redisstr['TotalAmount'];
$paymentType = $redisstr['paymentType'];
$currCodeType = $redisstr['currCodeType'];
$payerID =$redisstr['payer_id'];

$nvpstr='&TOKEN='.$token.'&PAYERID='.$payerID.'&PAYMENTACTION='.$paymentType.'&AMT='.$paymentAmount.'&CURRENCYCODE='.$currCodeType.'&IPADDRESS='.$serverName ;
//var_dump($nvpstr);exit;
Log::write("DoExpressCheckoutPayment:: nvpstr ".$nvpstr, "log");
//echo $nvpstr;

 /* Make the call to PayPal to finalize payment
    If an error occured, show the resulting errors
    */
$resArray=hash_call("DoExpressCheckoutPayment",$nvpstr);
/*
foreach($resArray as $key => $value) {
    Log::write("DoExpressCheckoutPayment::foreach   key:  ".$key."  value: ".$value, "log");
}
Log::write("ReviewOrder::GetExpressCheckoutDetails   session2:  ".$_SESSION['invnm']."  ".$_SESSION['email'], "log");
 */
$redisstr['paymentinfo_0_paymenttype'] = $resArray['PAYMENTINFO_0_PAYMENTTYPE'];
$redisstr['TIMESTAMP'] = $resArray['TIMESTAMP'];
$redisstr['paymentinfo_0_transactionid'] = $resArray['PAYMENTINFO_0_TRANSACTIONID'];
$redisstr['paymentinfo_0_transactiontype'] = $resArray['PAYMENTINFO_0_TRANSACTIONTYPE'];
$redisstr['paymentinfo_0_paymentstatus'] = $resArray['PAYMENTINFO_0_PAYMENTSTATUS'];
$redisstr['paymentinfo_0_ordertime'] = $resArray['PAYMENTINFO_0_ORDERTIME'];
$redisstr['paymentinfo_0_pendingreason'] = $resArray['PAYMENTINFO_0_PENDINGREASON'];
$redisstr['paymentinfo_0_reasoncode'] = $resArray['PAYMENTINFO_0_ERRORCODE'];
$redisstr['TotalAmount'] = $resArray['PAYMENTINFO_0_AMT'];

$redis->set($token, json_encode($redisstr));
$redis->close();
/* Display the API response back to the browser.
   If the response from PayPal was a success, display the response parameters'
   If the response was an error, display the errors received using APIError.php.
   */
$ack = strtoupper($resArray["ACK"]);


if($ack != 'SUCCESS' && $ack != 'SUCCESSWITHWARNING'){
    Log::write("DoExpressCheckoutPayment::ack  err 33 ", "log");
	$_SESSION['reshash']=$resArray;
	$location = "APIError.php";
    $sHtml = "<script>window.location = '".$location."'</script>";
    echo $sHtml;
   } else {
        require_once("service/paypal_responce_return.class.php");
    Log::write("DoExpressCheckoutPayment::ack  success ", "log");
        /**
         * 以下是业务逻辑处理层
         */
        $returnpay  = new PayResponce();
        $returnpay->recordRequest($token);
        echo "success";		//请不要修改或删除
   }

?>


<html>
<head>
    <title>PayPal PHP SDK - DoExpressCheckoutPayment API</title>
    <link href="sdk.css" rel="stylesheet" type="text/css" />
</head>
<body>
		<br>
		<center>
		<font size=2 color=black face=Verdana><b>DoExpressCheckoutPage</b></font>
		<br><br>

		<b>Order Processed! Thank you for your payment!</b><br><br>


    <table width =400>
                                        
         <?php 
   		 	require_once 'ShowAllResponse.php';
    	 ?>
    </table>
    </center>
    <a class="home" id="CallsLink" href="index.html">Home</a>
</body>
</html>