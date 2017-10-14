<?php
	$db =
	[
		SITE_MODE_DEV	=>
		[
			'pdo_driver'	=> 'mysql',
			'username'		=> 'username',
			'password'		=> 'password',
			'socket'		=> '127.0.0.1',	// Either unix socket or host[:port]
			'database'		=> 'database_name_dev'
		],
		SITE_MODE_PROD	=>
		[
			'pdo_driver'	=> 'mysql',
			'username'		=> 'username',
			'password'		=> 'password',
			'socket'		=> '127.0.0.1',
			'database'		=> 'database_name_prod'
		],
		SITE_MODE_TEST	=>
		[
			'pdo_driver'	=> 'mysql',
			'username'		=> 'username',
			'password'		=> 'password',
			'socket'		=> '127.0.0.1',
			'database'		=> 'database_name_test'
		]
	];