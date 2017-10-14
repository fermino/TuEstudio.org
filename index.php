<?php
	require __DIR__.'/config/main.php';

	require __DIR__.'/vendor/autoload.php';

	require __DIR__.'/simple_framework.php';

	use Monolog\Logger;
	use Monolog\Handler\StreamHandler;

	// Set up Monolog

	$logger = new Logger(SITE_MODE);
	$logger->pushHandler(new StreamHandler(__DIR__.'/log/' . strtolower(SITE_MODE) . '_main.log'));

	// Do it all (?

	$fw = new SimpleFramework($site_uri, $db, $routes, $logger);

	if(!$fw->handleRequest($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']))
	{
		$logger->critical('No response could be sent');

		exit;
	}