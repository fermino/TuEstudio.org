<?php
	// Do not touch this!
	if(defined('SITE_MODE'))
		exit;

	/**
	 * SITE_MODE:
	 *	0 = development
	 *	1 = production
	 *	2 = test
	 */

	define('SITE_MODE', 0);

	$cfg = array
	(
		'vendor_path'	=> '../vendor/',
		'dbs'			=> array
		(
			'development'	=> array
			(
				'pdo_driver'	=> 'mysql',
				'username'		=> 'username',
				'password'		=> 'password',
				'socket'		=> '127.0.0.1',	// Either unix socket or host[:port]
				'database'		=> 'database_name_dev'
			),
			'production'	=> array
			(
				'pdo_driver'	=> 'mysql',
				'username'		=> 'username',
				'password'		=> 'password',
				'socket'		=> '127.0.0.1',
				'database'		=> 'database_name_prod'
			),
			'test'			=> array
			(
				'pdo_driver'	=> 'mysql',
				'username'		=> 'username',
				'password'		=> 'password',
				'socket'		=> '127.0.0.1',
				'database'		=> 'database_name_test'
			)
		)
	);