<?php
/**
 *数据库连接相关参数
 *
 * @var unknown_type
 */
$g_arr_db_config = array(
		"host" =>"localhost",
		"user" => "root",
		"pwd"  => "1234",
		"type" => "commit",
		"coding" => "utf8",
		"db"   => "db_gt_third_party"
);
$g_notify_wall = 'http://wallet.turingcat.com/api/wallet/paymentSuccess';

define("TRADE_SUCCESS",  "1");//支付成功
define("TRADE_FINISHED", "2");//交易完成