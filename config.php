<?php
	// Do not touch this!
	if(defined('SITE_MODE') || defined('SITE_MODE_DEV') || defined('SITE_MODE_TEST') || defined('SITE_MODE_PROD'))
		exit;

	const SITE_MODE_DEV		= 'development';
	const SITE_MODE_PROD	= 'production';
	const SITE_MODE_TEST	= 'test';

	/**
	 * SITE_MODE:
	 *	SITE_MODE_DEV	= development
	 *	SITE_MODE_PROD	= production
	 *	SITE_MODE_TEST	= test
	 */

	const SITE_MODE	= SITE_MODE_DEV;

	$cfg =
	[
		'site_uri'		=> '/', // /tuestudio => without ending slash

		SITE_MODE_DEV	=>
		[
			'db' =>
			[
				'pdo_driver'	=> 'mysql',
				'username'		=> 'username',
				'password'		=> 'password',
				'socket'		=> '127.0.0.1',	// Either unix socket or host[:port]
				'database'		=> 'database_name_dev'
			]
		],
		SITE_MODE_PROD	=>
		[
			'db' =>
			[
				'pdo_driver'	=> 'mysql',
				'username'		=> 'username',
				'password'		=> 'password',
				'socket'		=> '127.0.0.1',
				'database'		=> 'database_name_prod'
			]
		],
		SITE_MODE_TEST	=>
		[
			'db' =>
			[
				'pdo_driver'	=> 'mysql',
				'username'		=> 'username',
				'password'		=> 'password',
				'socket'		=> '127.0.0.1',
				'database'		=> 'database_name_test'
			]
		]
	];