<?php
/* *
 * 功能：手机网站支付接口接入页
 * 版本：3.3
 * 修改日期：2012-07-23
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。
 */
require_once 'CallerService.php';
require_once 'service/lib/WriteLog.lib.php';
require_once "service/configs/config.php";
require_once("lib/Fnlib.php");

/* An express checkout transaction starts with a token, that
   identifies to PayPal your transaction
   In this example, when the script sees a token, the script
   knows that the buyer has already authorized payment through
   paypal.  If no token was found, the action is to send the buyer
   to PayPal to first authorize payment
   */

Log::write("ReviewOrder:: paypalorder", "log");

if(!isset($_REQUEST['token'])) {

    $dataTemp = $_POST['info'];
      $deData  = Fnlib::declassify($dataTemp);
      $_POST   = json_decode($deData,true);
    //支付成功跳转页面
    $return_url = "http://www.turingcat.com/";
    //需http://格式的完整路径，不能加?id=123这类自定义参数，不能写成http://localhost/
    //商户订单号
        $out_trade_no = $_POST['WIDout_trade_no'];
    //商户网站订单系统中唯一订单号，必填
    //订单名称
        $subjectname = $_POST['WIDsubject'];
    //必填
    //付款金额
        $total_fee = $_POST['WIDtotal_fee'];
    //必填
    //商品展示地址
        $show_url = $_POST['WIDshow_url'];
    //必填，需以http://开头的完整路径，例如：http://www.商户网址.com/myorder.html
    //订单描述
        $body = $_POST['WIDbody'];

    /* The servername and serverport tells PayPal where the buyer
       should be directed back to after authorizing payment.
       In this case, its the local webserver that is running this script
       Using the servername and serverport, the return URL is the first
       portion of the URL that buyers will return to after authorizing payment
       */
    $serverName = $_SERVER['SERVER_NAME'];
    $serverPort = $_SERVER['SERVER_PORT'];
    $url=dirname('http://'.$serverName.':'.$serverPort.$_SERVER['REQUEST_URI']);

    /*	   $currencyCodeType= "USD";
           $paymentType= "sale";
           //item infomation
           $L_NAME0           = "turingcat";
           $L_AMT0            = "10";
           $L_QTY0            =	1;
           $invnm = '122332434345345410 ';
           $sellerId = 'seller@mail.com';
           $desc = 'good product';       */

    $currencyCodeType= "USD";
    $paymentType= "sale";
    $L_QTY0  = 1;
    $L_AMT0  = $total_fee;
    $L_NAME0  = $subjectname;
    $invnm =  $out_trade_no;
    //  $sellerId = $_GET['extern_token'];
    $desc = $body;
    $perreutrnurl =  $return_url;
    $showurl = $show_url;
    //页面跳转同步通知页面路径
    /*   $_POST['return_url'] = "http://www.turingcat.com/";

       $L_AMT0  = $_POST['WIDtotal_fee'];
       $L_NAME0  = $_POST['WIDsubject'];
       $invnm =  $_POST['WIDout_trade_no'];
       $sellerId = $_POST['WIDextern_token'];
       $desc = $_POST['WIDbody'];
   */
    /*  //产生订单表数据
      require_once 'service/paypal_order.php';
      $paypalorder = new paypal_order();
      $paypalorder->insertOrderInfo($_POST);
    */
    /* The returnURL is the location where buyers return when a
       payment has been succesfully authorized.
       The cancelURL is the location buyers are sent to when they hit the
       cancel button during authorization of payment during the PayPal flow
       */

    $returnURL =urlencode($url.'/paypalapi.php?currencyCodeType='.$currencyCodeType.'&paymentType='.$paymentType);
    $cancelURL =urlencode("$url/SetExpressCheckout.php?paymentType=$paymentType" );

    /* Construct the parameter string that describes the PayPal payment
       the varialbes were set in the web form, and the resulting string
       is stored in $nvpstr
       */

    $itemamt = $L_AMT0;
    $amt = $itemamt;
    $nvpstr="";


    /*
     * Setting up the Shipping address details
     */

    $nvpstr="&PAYMENTREQUEST_0_INVNUM=".$invnm."&NOSHIPPING=1&PAYMENTREQUEST_0_CURRENCYCODE=".$currencyCodeType
        ."&L_PAYMENTREQUEST_0_NAME0=".$L_NAME0."&L_PAYMENTREQUEST_0_AMT0=".$L_AMT0."&L_PAYMENTREQUEST_0_QTY0=".$L_QTY0."&PAYMENTREQUEST_0_DESC=".$desc."&PAYMENTREQUEST_0_AMT="
        .$amt."&PAYMENTREQUEST_0_ITEMAMT=".$itemamt."&PAYMENTREQUEST_0_PAYMENTACTION=".$paymentType."&ReturnUrl=".$returnURL."&CANCELURL=".$cancelURL;


    //Log::write("ReviewOrder::get token: 332".$nvpstr, "log");
    /*If the API call succeded, then redirect the buyer to PayPal
    to begin to authorize payment.  If an error occured, show the
    resulting errors
    */
    $resArray=hash_call("SetExpressCheckout",$nvpstr);
    // $_SESSION['reshash']=$resArray;

    $ack = strtoupper($resArray["ACK"]);

    Log::write("ReviewOrder::get tokenss: ack ".$ack, "log");
    if($ack=="SUCCESS"){
        // Redirect to paypal.com here
        $token = urldecode($resArray["TOKEN"]);
        Log::write("ReviewOrder::get token: ".$token." success", "log");

        //set redis data
        $reisdata = array("token" => $token,
            "TotalAmount" => $amt,
            "subject" => $L_NAME0,
            "invnm" => $invnm,
            "returnurl" => $perreutrnurl,
            "desc" => $desc,
            "showurl" => $showurl);
        global $g_arr_redis_config;
        $redis = new Redis();
        $redis->connect($g_arr_redis_config['host'], $g_arr_redis_config['port']);
        $redis->auth($g_arr_redis_config['pwd']);
        $redis->set($token, json_encode($reisdata));
        $redis->close();
        $payPalURL = PAYPAL_URL.$token;
        //header("Location: ".$payPalURL);
        //var_dump($payPalURL);exit;
        $sHtml = "<script>window.location = '".$payPalURL."'</script>";
        echo $sHtml;exit;
    } else  {
        //Redirecting to APIError.php to display errors.
        $_SESSION['reshash'] = $resArray;
        Log::write("ReviewOrder::get token:  false", "log");
        $location = "APIError.php";
        //header("Location: $location");
        $sHtml = "<script>window.location = '".$location."'</script>";
        echo $sHtml;
    }

} else {
    /* At this point, the buyer has completed in authorizing payment
       at PayPal.  The script will now call PayPal with the details
       of the authorization, incuding any shipping information of the
       buyer.  Remember, the authorization is not a completed transaction
       at this state - the buyer still needs an additional step to finalize
       the transaction
       */

    $token =urlencode( $_REQUEST['token']);

    Log::write("ReviewOrder::pay return token:".$token."     query string: ".$_SERVER["QUERY_STRING"], "log");

    /* Build a second API request to PayPal, using the token as the
       ID to get the details on the payment authorization
       */
    $nvpstr="&TOKEN=".$token;


    /* Make the API call and store the results in an array.  If the
       call was a success, show the authorization details, and provide
       an action to complete the payment.  If failed, show the error
       */
    $resArray=hash_call("GetExpressCheckoutDetails",$nvpstr);
    $_SESSION['reshash']=$resArray;
    $ack = strtoupper($resArray["ACK"]);

    /*   foreach($resArray as $key => $value) {
           Log::write("ReviewOrder::GetExpressCheckoutDetails   key:  ".$key."  value: ".$value, "log");
       }
   */

    if($ack == 'SUCCESS' || $ack == 'SUCCESSWITHWARNING'){
        Log::write("ReviewOrder::GetExpressCheckoutDetails   success  ", "log");
        require_once "GetExpressCheckoutDetails.php";
        /*   $_SESSION['invnm']=$resArray['PAYMENTREQUEST_0_INVNUM'];
           $_SESSION['subject']=$resArray['L_PAYMENTREQUEST_0_NAME0'];
           $_SESSION['email'] = $resArray['EMAIL'];
           $_SESSION['desc'] = $resArray['PAYMENTREQUEST_0_DESC'];
           $_SESSION['bank_seq_no'] = $resArray['TOKEN'];
           $_SESSION['TotalAmount'] = $resArray['PAYMENTREQUEST_0_AMT'];
           $_SESSION['sellerId'] = $resArray['PAYMENTREQUEST_0_SELLERPAYPALACCOUNTID'];
           $_SESSION['desc'] = $resArray['PAYMENTREQUEST_0_DESC'];
           $_SESSION['TIMESTAMP'] = $resArray['TIMESTAMP']; */

        global $g_arr_redis_config;
        $redis = new Redis();
        $redis->connect($g_arr_redis_config['host'], $g_arr_redis_config['port']);
        $redis->auth($g_arr_redis_config['pwd']);
        $redisstr = json_decode($redis->get($token), true);
        $redisstr['buyeremail'] = $resArray['EMAIL'];
        $redisstr['TIMESTAMP'] = $resArray['TIMESTAMP'];
        $redisstr['sellerId'] = $resArray['PAYMENTREQUEST_0_SELLERPAYPALACCOUNTID'];
        $redis->set($token, json_encode($redisstr));
        $redis->close();
        require_once("service/paypal_responce_return.class.php");
        $returnpay  = new PayResponce();
        $returnpay->recordDetailRequest($token);

        //  Log::write("ReviewOrder::GetExpressCheckoutDetails   session1:  ".$_SESSION['invnm']."  ".$_SESSION['email']."  ".$_SESSION['subject']."  TotalAmount".$_SESSION['TotalAmount'], "log");
    } else  {
        //Redirecting to APIError.php to display errors.
        Log::write("ReviewOrder::GetExpressCheckoutDetails  woring 1","log");
        $location = "APIError.php";
       // header("Location: $location");
        $sHtml = "<script>window.location = '".$location."'</script>";
        echo $sHtml;
    }
}