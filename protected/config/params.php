<?php
return array(
		/**
		 * 公共参数配置
		 */
		'expire_in'	=> 18000,
		
		
		/**
		 * 本地测试配置
		 */
		'third_party'=>array(
				'wash8'=>array(
						'third_check_available'	=> 'http://wash8.net/m/thirdparty/turingcat/available',
						'payment-notify'        => 'http://172.16.45.128/m/thirdparty/turingcat/payment-notify'
				),
		),
		'third_api_interface'   => array(
				'1'=>'http://turingcate.guangtian.com/alipay/alipayapi.php',
				'2'=>'http://turingcate.guangtian.com/alipay_pc/alipayapi.php',
				'3'=>'http://turingcate.guangtian.com/paypal/paypalapi.php',
				'4'=>'http://turingcate.guangtian.com/alipay_mall_pc/alipayapi.php'
		),
		'mall_return_url'       => '',
		'mall_notify_url'       => '',
		'msg'                   => array(
			'accountSid'	=> 'aaf98f894e2360b4014e24873db60226',
			'accountToken'  => '9c112cef17b74a599f0cd8fc97ae19ec',
			'appId'     	=> 'aaf98f894e2360b4014e248796d40229',
			'serverIP'  	=> 'sandboxapp.cloopen.com',
			'serverPort'    => '8883',
			'softVersion'   => '2013-12-26'
		),
		'api_pay'=>array(
				'query_money'=>'http://120.24.81.23:18888/api/query/money',//查询余额
				'consume_money'=>'http://120.24.81.23:18888/api/consume',//预扣费
				'post_consume'=>'http://120.24.81.23:18888/api/postconsume'//确认扣费
		),
		'heal_config'=>array(
				'app_param'=>array(
						'clientKey'=>'IdepXCkJaNjzucAq',//'af79uX0l2fcvwLkk'
						'clientSecret'=>'F0BbzdxHt8x0lmKBxUZqH70k40Ia93bz',//'UkYwbU03905kFXDD7Bu90oB8UmI5qbxt',//
						'redirect_uri'=>'http://turingcate.guangtian.com/heal/home',//'http://heal.turingcat.com/misfit/back',//
						'apiRoot'=>'https://api.misfitwearables.com',
						'response_type'=> 'code',
						'scope'=>'public,email,birthday,tracking,sessions,sleeps',
				),
				'endpoint_param'=>array(
						'authorize'=> '/auth/dialog/authorize',
						'exchange'=> '/auth/tokens/exchange',
						'profile'=> '/move/resource/v1/user/:userId/profile',
						'profileWithId'=> '/move/resource/v1/user/:userId/profile/:id',
						'device'=> '/move/resource/v1/user/:userId/device',
						'deviceWithId'=> '/move/resource/v1/user/:userId/device/:id',
						'goal'=> '/move/resource/v1/user/:userId/activity/goals',
						'goalWithId'=> '/move/resource/v1/user/:userId/activity/goals/:id',
						'summary'=> '/move/resource/v1/user/:userId/activity/summary',
						'session'=> '/move/resource/v1/user/:userId/activity/sessions',
						'sessionWithId'=> '/move/resource/v1/user/:userId/activity/sessions/:id',
						'sleep'=> '/move/resource/v1/user/:userId/activity/sleeps',
						'sleepWithId'=> '/move/resource/v1/user/:userId/activity/sleeps/:id',
						'push'=> '/shine/v7/open/push',
						'batchRequest'=> '/shine/v7/open/batch_request',
						'revoke'=> '/auth/tokens/revoke',
				),
				'third_info'=>array(
						'direct_url' => 'http://172.16.45.57:9000/api/device/addMisfit'
				)
		),
		'call_config'=>array(
				'appId' => '5000184',
				'token' => 'b9c97bae8f19d72e57721615e1766398',
				'webcall'=> 'http://api.vlink.cn/interface/open/v1/webcall'
		)
		
		
		
		/**
		 * 正式配置
		 */
// 		'third_party'=>array(
// 				'wash8'=>array(
// 						'third_check_available'	=> 'http://wash8.net/m/thirdparty/turingcat/available',
// 						'payment-notify'        => 'http://wash8.net/m/thirdparty/turingcat/payment-notify'
// 				),
// 		),
// 		'third_api_interface'   => array(
// 				'1'=>'http://pay.turingcat.com/alipay/alipayapi.php',
// 				'2'=>'http://pay.turingcat.com/alipay_pc/alipayapi.php',
// 				'3'=>'http://pay.turingcat.com/paypal/paypalapi.php'
// 		),
// 		'mall_return_url'       => '',
// 		'mall_notify_url'       => '',
// 		'msg'                   => array(
// 				'accountSid'	=> 'aaf98f894e2360b4014e24873db60226',
// 				'accountToken'  => '9c112cef17b74a599f0cd8fc97ae19ec',
// 				'appId'     	=> 'aaf98f894e2360b4014e248796d40229',
// 				'serverIP'  	=> 'sandboxapp.cloopen.com',
// 				'serverPort'    => '8883',
// 				'softVersion'   => '2013-12-26'
// 		),
// 		'api_pay'=>array(
// 				'query_money'=>'http://10.169.110.191:18888/api/query/money',//查询余额
// 				'consume_money'=>'http://10.169.110.191:18888/api/consume',//预扣费
// 				'post_consume'=>'http://10.169.110.191:18888/api/postconsume'//确认扣费
// 		),
// 		'heal_config'=>array(
// 				'app_param'=>array(
// 						'clientKey'=>'IdepXCkJaNjzucAq',//'af79uX0l2fcvwLkk'
// 						'clientSecret'=>'F0BbzdxHt8x0lmKBxUZqH70k40Ia93bz',//'UkYwbU03905kFXDD7Bu90oB8UmI5qbxt',//
// 						'redirect_uri'=>'http://pay.turingcat.com/heal/home',//'http://heal.turingcat.com/misfit/back',//
// 						'apiRoot'=>'https://api.misfitwearables.com',
// 						'response_type'=> 'code',
// 						'scope'=>'public,email,birthday,tracking,sessions,sleeps',
// 				),
// 				'endpoint_param'=>array(
// 						'authorize'=> '/auth/dialog/authorize',
// 						'exchange'=> '/auth/tokens/exchange',
// 						'profile'=> '/move/resource/v1/user/:userId/profile',
// 						'profileWithId'=> '/move/resource/v1/user/:userId/profile/:id',
// 						'device'=> '/move/resource/v1/user/:userId/device',
// 						'deviceWithId'=> '/move/resource/v1/user/:userId/device/:id',
// 						'goal'=> '/move/resource/v1/user/:userId/activity/goals',
// 						'goalWithId'=> '/move/resource/v1/user/:userId/activity/goals/:id',
// 						'summary'=> '/move/resource/v1/user/:userId/activity/summary',
// 						'session'=> '/move/resource/v1/user/:userId/activity/sessions',
// 						'sessionWithId'=> '/move/resource/v1/user/:userId/activity/sessions/:id',
// 						'sleep'=> '/move/resource/v1/user/:userId/activity/sleeps',
// 						'sleepWithId'=> '/move/resource/v1/user/:userId/activity/sleeps/:id',
// 						'push'=> '/shine/v7/open/push',
// 						'batchRequest'=> '/shine/v7/open/batch_request',
// 						'revoke'=> '/auth/tokens/revoke',
// 				)
// 		),
);