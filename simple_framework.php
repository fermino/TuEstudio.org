<?php
	const HTTP_OK	= 200;

	const HTTP_FORBIDDEN			= 403;
	const HTTP_NOT_FOUND			= 404;
	const HTTP_METHOD_NOT_ALLOWED	= 405;

	const HTTP_INTERNAL_SERVER_ERROR	= 500;

	require __DIR__.'/controllers/application.php';

	use Psr\Log\LoggerInterface;

	class SimpleFramework
	{
		private $router	= null;
		private $logger	= null;

		public function __construct(array $db, array $routes, LoggerInterface $logger)
		{
			$this->logger	= $logger;

			$this->initializeORM($db);
			$this->initializeRouter($routes);
		}

		private function initializeORM(array $db)
		{
			ActiveRecord\Config::initialize(function(ActiveRecord\Config $orm_cfg) use ($db)
			{
				$orm_cfg->set_model_directory(realpath(__DIR__ . '/models/'));

				$connections =
				[
					// pdo_driver://username:password@socket/database?charset=utf8

					SITE_MODE_DEV => "{$db[SITE_MODE_DEV]['pdo_driver']}://{$db[SITE_MODE_DEV]['username']}:{$db[SITE_MODE_DEV]['password']}@{$db[SITE_MODE_DEV]['socket']}/{$db[SITE_MODE_DEV]['database']}?charset=utf8",
					SITE_MODE_PROD => "{$db[SITE_MODE_PROD]['pdo_driver']}://{$db[SITE_MODE_PROD]['username']}:{$db[SITE_MODE_PROD]['password']}@{$db[SITE_MODE_PROD]['socket']}/{$db[SITE_MODE_PROD]['database']}?charset=utf8",
					SITE_MODE_TEST => "{$db[SITE_MODE_TEST]['pdo_driver']}://{$db[SITE_MODE_TEST]['username']}:{$db[SITE_MODE_TEST]['password']}@{$db[SITE_MODE_TEST]['socket']}/{$db[SITE_MODE_TEST]['database']}?charset=utf8"
				];

				$orm_cfg->set_connections($connections);
				$orm_cfg->set_default_connection(SITE_MODE);
			});

			spl_autoload_register(function($class)
			{
				$class[0] = strtolower($class[0]);
				$class = preg_replace_callback('/([A-Z])/', function($c) { return '_' . strtolower($c[1]); }, $class);
				$class = __DIR__.'/models/' . strtolower($class) . '.php';

				if(is_readable($class))
					include $class;
			});
		}

		private function initializeRouter(array $routes)
		{
			$this->router = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) use($routes)
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
		}

		public function handleRequest(string $request_method, string $request_uri) : bool
		{
			// Strip query string (?foo=bar) and decode URI
			if(false !== ($pos = strpos($request_uri, '?')))
				$request_uri = substr($request_uri, 0, $pos);

			$request_uri = rawurldecode($request_uri);

			// Dispatch the route
			$route_info = $this->router->dispatch($request_method, $request_uri);

			switch($route_info[0])
			{
				case FastRoute\Dispatcher::FOUND:
					return $this->loadController($route_info[1], $request_method, $route_info[2]);
					break;
				case FastRoute\Dispatcher::NOT_FOUND:
					return $this->sendResponse(HTTP_NOT_FOUND);
					break;
				case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
					return $this->sendResponse(HTTP_METHOD_NOT_ALLOWED, $route_info[1]);
					break;
			}

			return false;
		}

		private function loadController(string $controller, string $request_method, array $route_params) : bool
		{
			$controller = strtolower($controller);

			$controller_path	= __DIR__.'/controllers/' . $controller . '.php';
			$helper_path 		= __DIR__.'/helpers/' . $controller . '.php';

			if(is_readable($helper_path))
				include $helper_path;

			if(is_readable($controller_path))
			{
				// Lint?

				include $controller_path;

				$class_name = str_replace('_', null, ucwords($controller, '_')) . 'Controller';

				if(class_exists($class_name))
				{
					if(is_subclass_of($class_name, 'ApplicationController'))
					{
						$controller = new $class_name($this->logger);

						$response = $controller->handleRequest($request_method, $route_params);

						if(null === $response)
							return true;

						return $this->sendResponse($response);
					}

					$this->logger->critical('[SimpleFramework::loadController] Controller must extend ControllerBase',
					[
						'controller'	=> $controller,
						'method'		=> $request_method,
						'route_params'	=> $route_params
					]);
					return $this->sendResponse(HTTP_INTERNAL_SERVER_ERROR);
				}

				$this->logger->critical('[SimpleFramework::loadController] Controller class not exist',
				[
					'controller'	=> $controller,
					'method'		=> $request_method,
					'route_params'	=> $route_params
				]);
				return $this->sendResponse(HTTP_INTERNAL_SERVER_ERROR);
			}

			$this->logger->critical('[SimpleFramework::loadController] Controller file not readable',
			[
				'controller'	=> $controller,
				'method'		=> $request_method,
				'route_params'	=> $route_params
			]);
			return $this->sendResponse(HTTP_INTERNAL_SERVER_ERROR);
		}

		private function sendResponse($response, array $data = []) : bool
		{
			if(null === $response)
				return true; // $response = HTTP_OK

			if(!headers_sent())
			{
				if(is_string($response))
				{
					http_response_code(200); // 200 OK
					header('Location: ' . $response);

					return true;
				}
				else if(is_int($response))
				{
					switch($response)
					{
						case HTTP_FORBIDDEN:
							http_response_code(HTTP_FORBIDDEN); // 403 Forbidden
							echo HTTP_FORBIDDEN;
							//Load template

							return true;

						case HTTP_NOT_FOUND:
							http_response_code(HTTP_NOT_FOUND); // 404 Not Found
							echo HTTP_NOT_FOUND;
							//Load template

							return true;

						case HTTP_METHOD_NOT_ALLOWED:
							http_response_code(HTTP_METHOD_NOT_ALLOWED); // 405 Method Not Allowed
							header('Allow: ' . implode(', ', $data));

							echo HTTP_METHOD_NOT_ALLOWED;
							//Load template

							return true;

						case HTTP_INTERNAL_SERVER_ERROR:
							http_response_code(HTTP_INTERNAL_SERVER_ERROR); // 500 Internal Server Error
							echo HTTP_INTERNAL_SERVER_ERROR;

							//Load template

							return true;

						case HTTP_OK:
							http_response_code(HTTP_OK); // 200 OK
							return true;

						// default:
					}
				}
			}

			return false;
		}
	}