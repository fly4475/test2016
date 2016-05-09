<?php
/********************************************************
GetExpressCheckoutDetails.php

This functionality is called after the buyer returns from
PayPal and has authorized the payment.

Displays the payer details returned by the
GetExpressCheckoutDetails response and calls
DoExpressCheckoutPayment.php to complete the payment
authorization.

Called by ReviewOrder.php.

Calls DoExpressCheckoutPayment.php and APIError.php.

********************************************************/

//session_start();

/* Collect the necessary information to complete the
   authorization for the PayPal payment
   */
/*
foreach($_REQUEST as $key => $value) {
    Log::write("GetExpressCheckoutDetails 333  key:  ".$key."  value: ".$value, "log");
}
*/
/*
$_SESSION['token']=$_REQUEST['token'];
$_SESSION['payer_id'] = $_REQUEST['PayerID'];

//$_SESSION['paymentAmount']=$_REQUEST['paymentAmount'];
$_SESSION['currCodeType']= 'USD';
$_SESSION['paymentType']=$_REQUEST['paymentType']?$_REQUEST['paymentType']:'';


$resArray=$_SESSION['reshash'];
$_SESSION['TotalAmount']= $resArray['AMT'] + $resArray['SHIPDISCAMT'];  */
$_SESSION['token']=$_REQUEST['token'];
$redis = new Redis();
$redis->connect($g_arr_redis_config['host'], $g_arr_redis_config['port']);
$redis->auth($g_arr_redis_config['pwd']);
$redisstr = json_decode($redis->get($_REQUEST['token']), true);
$redisstr['payer_id'] = $_REQUEST['PayerID'];
$redisstr['currCodeType'] =  'USD';
$redisstr['paymentType'] =  $_REQUEST['paymentType']?$_REQUEST['paymentType']:'';
$redis->set($_REQUEST['token'], json_encode($redisstr));
$redis->close();
/* Display the  API response back to the browser .
   If the response from PayPal was a success, display the response parameters
   */

?>



<html>
<head>
    <title>PayPal NVP SDK - ExpressCheckout-Instant API- Simplified Order Review Page</title>
    <link href="sdk.css" rel="stylesheet" type="text/css" />
</head>
<body>
<center>
	<font size=2 color=black face=Verdana><b>Loading....</b></font>
	<br><br></center>
	<form action="DoExpressCheckoutPayment.php" method="POST" name="mainform">
	 <center>
           <table width =270>
             <tr>
		               <td colspan="2" class="header">
		                   <!--Step 3: DoExpressCheckoutPayment -->
                           <input type="hidden" name="token" value="<?php echo $_REQUEST['token'] ?>">
		               </td>
          </tr>
            <tr>
                <td><b></b></td>
                <td>
                  <?php  //echo $_REQUEST['currencyCodeType'];   echo $resArray['AMT'] + $resArray['SHIPDISCAMT'] ?></td>
            </tr>
            
 		<?php
   		 	//require_once 'ShowAllResponse.php';
   		 ?>
          
            <tr>
                <td class="thinfield">
				<!-- 自动提交订单 -->
                <script language="JavaScript" type="text/JavaScript">mainform.submit();</script> 
                </td>
            </tr>
        </table>
    </center>
    </form>

</body>
</html>
