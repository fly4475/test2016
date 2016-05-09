<?php
/**
 *数据库连接相关参数
 *
 * @var unknown_type
 */
// $g_arr_db_config = array(
// 		"host" =>"localhost",
// 		"user" => "root",
// 		"pwd"  => "1234",
// 		"type" => "commit",
// 		"coding" => "utf8",
// 		"db"   => "db_gt_third_party"
// );

// $g_arr_redis_config = array(
//     "host" =>"127.0.0.1",
//     "port" => "6379",
//     "pwd"  => "1234"
// );
// $g_notify_wall = 'http://wallet.turingcat.com/api/wallet/paymentSuccess';

// define("TRADE_SUCCESS",  "1");//支付成功
// define("TRADE_FINISHED", "2");//交易完成

/**
 * 以下为正式测试环境
 */
$g_arr_db_config = array(
		"host" =>"rdsz1pxl7n91g8kbjm60.mysql.rds.aliyuncs.com",
		"user" => "jet",
		"pwd"  => "Trd_FE-jet-_123",
		"type" => "commit",
		"coding" => "utf8",
		"db"   => "db_gt_third_party"
);

$g_arr_redis_config = array(
		"host" =>"10.169.136.51",
		"port" => "6379",
		"pwd"  => "GuangtianTuringCatGood"
);
$g_notify_wall = 'http://wallet.turingcat.com/api/wallet/paymentSuccess';

define("TRADE_SUCCESS",  "1");//支付成功
define("TRADE_FINISHED", "2");//交易完成