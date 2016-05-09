<?php

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'Turingcat Api Interface',

	// preloading 'log' component
	'preload'=>array('log'),

	// autoloading model and component classes
	'import'=>array(
		'application.models.*',
		'application.components.*',
		'ext.YiiMongoDbSuite.*',
	),

	'modules'=>array(
		// uncomment the following to enable the Gii tool
		/*
		'gii'=>array(
			'class'=>'system.gii.GiiModule',
			'password'=>'Enter Your Password Here',
			// If removed, Gii defaults to localhost only. Edit carefully to taste.
			'ipFilters'=>array('127.0.0.1','::1'),
		),
		*/
	),

	// application components
	'components'=>array(

		'user'=>array(
			// enable cookie-based authentication
			'allowAutoLogin'=>true,
		),

		// uncomment the following to enable URLs in path-format

		'urlManager'=>array(
			'urlFormat'=>'path',
			'showScriptName'=>false,
			'rules'=>array(
				'<controller:\w+>/<id:\d+>'=>'<controller>/view',
				'<controller:\w+>/<action:\w+>/<id:\d+>'=>'<controller>/<action>',
				'<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
			),
		),


		// database settings are configured in database.php
		'db_heal'=> array(
			//'connectionString' => 'sqlite:'.dirname(__FILE__).'/../data/testdrive.db',
			// uncomment the following lines to use a MySQL database
			'class' => 'CDbConnection',
// 			'connectionString' => 'mysql:host=localhost;dbname=heal',
			'connectionString' => 'mysql:host=172.16.45.254;dbname=heal',
			'emulatePrepare' => true,
// 			'username' => 'root',
// 			'password' => 'cj1qaz2wsx',
			'username' => 'cooxm',
			'password' => 'cooxm',
			'charset' => 'utf8',
		),
			
		'mongodb' => array(
				'class'            => 'EMongoDB', //主文件
				'connectionString' => 'mongodb://127.0.0.1:27017', //服务器地址
				'dbName'           => 'mongodb_heal',//数据库名称
				'fsyncFlag'        => true, //mongodb的确保所有写入到数据库的安全存储到磁盘
				'safeFlag'         => true, //mongodb的等待检索的所有写操作的状态，并检查
				'useCursor'        => true,
		),

		'db'=>require(dirname(__FILE__).'/database.php'),
			

		'errorHandler'=>array(
			// use 'site/error' action to display errors
			'errorAction'=>'site/error',
		),

		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'info',
					'maxFileSize'=>1048576,//单文件最大1G
					'logFile'=>date('Y-m-d').'.log',
				),
				// uncomment the following to show log messages on web pages
				/*
				array(
					'class'=>'CWebLogRoute',
				),
				*/
			),
		),
		'redis_cache' => array (
// 				'class'    => 'system.caching.CRedisCache',
				'class'	   => 'application.components.ZRedisCache',
				'hostname' =>'127.0.0.1',
				'port'	   =>6379,
				'password' =>'1234',
				'database' =>8
		)

	),

	// application-level parameters that can be accessed
	// using Yii::app()->params['paramName']
    'params'=>require(dirname(__FILE__).'/params.php'),
);
