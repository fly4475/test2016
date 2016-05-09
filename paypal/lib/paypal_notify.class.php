<?php

class PaypalNotify {

    /**
     * 针对notify_url验证消息是否是paypal出的合法消息
     * @return 验证结果
     */
    function verifyNotify(){
        if(empty($_POST)) {//判断POST来的数组是否为空
            return false;
        }
        else {
            //从 PayPal  出读取 POST  信息同时添加变量?cmd?
            $req = 'cmd=_notify-validate';
            foreach ($_POST as $key => $value) {
                $value = urlencode(stripslashes($value));
                $req .= "&$key=$value";
            }
            $header= '';
            //建议在此将接受到的信息记录到日志文件中以确认是否收到 IPN  信息
            //将信息 POST  回给 PayPal  进行验证
            $header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
            $header .= "Content-Type:application/x-www-form-urlencoded\r\n";
            $header .= "Content-Length:" . strlen($req) ."\r\n\r\n";
            //在 Sandbox  情况下，设置：
            $fp = fsockopen("www.sandbox.paypal.com",80,$errno,$errstr,30);
           // $fp = fsockopen ('www.paypal.com', 80, $errno, $errstr, 30);
            //判断回复 POST  是否创建成功
            if (!$fp) {
                //HTTP  错误
                return false;
            }else {
                //将回复 POST  信息写入 SOCKET  端口
                fputs ($fp, $header .$req);
                //开始接受 PayPal  对回复 POST  信息的认证信息
                while (!feof($fp)) {
                    $res = fgets ($fp, 1024);
                    //已经通过认证
                    if (strcmp ($res, "VERIFIED") == 0) {
                        return true;
                    }else if (strcmp ($res, "INVALID") == 0) {
                        //未通过认证，有可能是编码错误或非法的 POST  信息
                        return false;
                    }
                }
            }
        }
    }








}
?>