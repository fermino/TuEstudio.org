<?php
	require __DIR__ . '/config.php';
	if(empty($cfg) || !is_array($cfg))
		exit;

	require __DIR__ . '/routes.php';
	if(empty($routes) || !is_array($routes))
		exit;

	require __DIR__ . '/vendor/autoload.php';
	require __DIR__ . '/class/controller_base.php';

	// Set up the ORM

	ActiveRecord\Config::initialize(function($orm_cfg) use ($cfg)
	{
		$orm_cfg->set_model_directory(__DIR__ . '/models/');

		$connections =
		[
			// pdo_driver://username:password@socket/database?charset=utf8

			SITE_MODE_DEV => "{$cfg[SITE_MODE_DEV]['db']['pdo_driver']}://{$cfg[SITE_MODE_DEV]['db']['username']}:{$cfg[SITE_MODE_DEV]['db']['password']}@{$cfg[SITE_MODE_DEV]['db']['socket']}/{$cfg[SITE_MODE_DEV]['db']['database']}?charset=utf8",
			SITE_MODE_PROD => "{$cfg[SITE_MODE_PROD]['db']['pdo_driver']}://{$cfg[SITE_MODE_PROD]['db']['username']}:{$cfg[SITE_MODE_PROD]['db']['password']}@{$cfg[SITE_MODE_PROD]['db']['socket']}/{$cfg[SITE_MODE_PROD]['db']['database']}?charset=utf8",
			SITE_MODE_TEST => "{$cfg[SITE_MODE_TEST]['db']['pdo_driver']}://{$cfg[SITE_MODE_TEST]['db']['username']}:{$cfg[SITE_MODE_TEST]['db']['password']}@{$cfg[SITE_MODE_TEST]['db']['socket']}/{$cfg[SITE_MODE_TEST]['db']['database']}?charset=utf8"
		];

		$orm_cfg->set_connections($connections);
		$orm_cfg->set_default_connection(SITE_MODE);
	});

	// Set up the router

	$fast_route = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) use($routes)
	{
		$add_route = function(FastRoute\RouteCollector $r, $routes) use(&$add_route)
		{
			foreach($routes as $route => $data)
			{
				// If the controller name is empty, it means the page is static
				// Filter first slash, convert further / to _, and delete everything after the first regex
				//if(empty($data))
				//	$data = [$route];

				if(!is_array($data))
					$data = [$data];

				// If data[0] is empty, there's no route to route (?
				if(!empty($data[0]))
				{
					// Sanitize data[1], the available methods for this route
					if(empty($data[1]))
						$data[1] = ['GET'];

					if(!is_array($data[1]))
						$data[1] = [$data[1]];

					// Add the route for every available method
					$r->addRoute(array_map('strtoupper', $data[1]), $route, $data[0]);
				}
				else
				{
					// If there's no data[0] (no controller) and data is an array,
					// the route should be treated as a group

					$r->addGroup($route, function(FastRoute\RouteCollector $r) use($data, &$add_route)
					{
						$add_route($r, $data);
					});
				}
			}
		};

		$add_route($r, $routes);
	});

	// Fetch method and URI from somewhere
	$request_method	= $_SERVER['REQUEST_METHOD'];
	$request_uri	= $_SERVER['REQUEST_URI'];

	// Strip query string (?foo=bar)and decode URI
	if(false !== ($pos = strpos($request_uri, '?')))
		$request_uri = substr($request_uri, 0, $pos);

	$request_uri = rawurldecode($request_uri);

	// Strip the site path (folder)
	if(!empty($cfg['site_uri']))
		$request_uri = substr($request_uri, strlen($cfg['site_uri']));

	// Dispatch the route
	$route_info = $fast_route->dispatch($request_method, $request_uri);
	$response = 500;

	switch($route_info[0])
	{
		case FastRoute\Dispatcher::FOUND:
			$route_info[1] = strtolower($route_info[1]);

			include __DIR__ . '/controllers/' . $route_info[1] . '.php';

			$class_name = str_replace('_', null, ucwords($route_info[1], '_')) . 'Controller';

			if(class_exists($class_name))
			{
				$controller = new $class_name;

				if($controller instanceof ControllerBase)
				{
					$response = $controller->handleRequest($_SERVER['REQUEST_METHOD'], $route_info[2]);
				}
				else
				{
					//Log
					$response = 500;
				}
			}
			else
			{
				//Log
				$response = 500;
			}
			break;
		case FastRoute\Dispatcher::NOT_FOUND:
			$response = 404;
			break;
		case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
			$response = 405;
			break;
	}

	switch($response)
	{
		case 0:
			// Everything went true
			break;
		case 404:
			http_response_code(404); // 404 Not Found
			echo '404';
			//Load template
			break;
		case 405:
			http_response_code(405); // 405 Method Not Allowed

			if(!headers_sent())
				header('Allow: ' . implode(', ', $route_info[1]));

			echo '405';
			break;
		case 500:
		default:
			http_response_code(500); // 500 Internal Server Error
			echo '500';
			break;
	}