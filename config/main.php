<?php
	// Do not touch this!
	if(defined('SITE_MODE') || defined('SITE_MODE_DEV') || defined('SITE_MODE_TEST') || defined('SITE_MODE_PROD'))
		exit;

	const SITE_MODE_DEV		= 'development';
	const SITE_MODE_PROD	= 'production';
	const SITE_MODE_TEST	= 'test';

/**
 * Start config
 */
	/**
	 * SITE_MODE:
	 *	SITE_MODE_DEV	= development
	 *	SITE_MODE_PROD	= production
	 *	SITE_MODE_TEST	= test
	 */

	const SITE_MODE	= SITE_MODE_DEV;

	$site_uri = ''; // Empty or path ONLY with start slash
/**
 * End config
 */

	require __DIR__.'/database.php';
	if(empty($db[SITE_MODE]) || !is_array($db[SITE_MODE]))
		exit;

	require __DIR__.'/routes.php';
	if(empty($routes) || !is_array($routes))
		exit;