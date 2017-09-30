<?php
	require __DIR__ . '/config.php';

	require __DIR__ . '/' . $cfg['vendor_path'] . '/autoload.php';

	// Set up the ORM

	ActiveRecord\Config::initialize(function($orm_cfg) use ($cfg)
	{
		$orm_cfg->set_model_directory(__DIR__ . '/models/');

		$connections =
		[
			SITE_MODE_DEV => "{$cfg[SITE_MODE_DEV]['db']['pdo_driver']}://{$cfg[SITE_MODE_DEV]['db']['username']}:{$cfg[SITE_MODE_DEV]['db']['password']}@{$cfg[SITE_MODE_DEV]['db']['socket']}/{$cfg[SITE_MODE_DEV]['db']['database']}?charset=utf8",
			SITE_MODE_PROD => "{$cfg[SITE_MODE_PROD]['db']['pdo_driver']}://{$cfg[SITE_MODE_PROD]['db']['username']}:{$cfg[SITE_MODE_PROD]['db']['password']}@{$cfg[SITE_MODE_PROD]['db']['socket']}/{$cfg[SITE_MODE_PROD]['db']['database']}?charset=utf8",
			SITE_MODE_TEST => "{$cfg[SITE_MODE_TEST]['db']['pdo_driver']}://{$cfg[SITE_MODE_TEST]['db']['username']}:{$cfg[SITE_MODE_TEST]['db']['password']}@{$cfg[SITE_MODE_TEST]['db']['socket']}/{$cfg[SITE_MODE_TEST]['db']['database']}?charset=utf8"
		];

		$orm_cfg->set_connections($connections);
		$orm_cfg->set_default_connection(SITE_MODE);
	});
