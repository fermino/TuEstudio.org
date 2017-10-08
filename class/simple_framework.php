<?php
	require __DIR__.'/controller_base.php';

	use Psr\Log\LoggerInterface;

	class SimpleFramework
	{
		private $cfg	= null;

		private $router	= null;
		private $logger	= null;

		public function __construct(array $cfg, array $routes, LoggerInterface $logger)
		{
			$this->cfg		= $cfg;
			$this->logger	= $logger;

			$this->initializeORM($this->cfg);
			$this->initializeRouter($routes);
		}

		private function initializeORM(array $cfg)
		{
			ActiveRecord\Config::initialize(function(ActiveRecord\Config $orm_cfg) use ($cfg)
			{
				$orm_cfg->set_model_directory(realpath(__DIR__ . '/../models/'));

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

			// Strip the site path (folder)
			if(!empty($this->cfg['site_uri']))
				$request_uri = substr($request_uri, strlen($this->cfg['site_uri']));

			// Dispatch the route
			$route_info = $this->router->dispatch($request_method, $request_uri);

			switch($route_info[0])
			{
				case FastRoute\Dispatcher::FOUND:
					return $this->loadController($route_info[1]);
					break;
				case FastRoute\Dispatcher::NOT_FOUND:
					return $this->sendResponse(404);
					break;
				case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
					return $this->sendResponse(405);
					break;
			}

			return false;
		}

		private function loadController(string $controller) : bool
		{
			$controller = strtolower($controller);
			$controller_path = __DIR__.'/../controllers/' . $controller . '.php';

			if(is_readable($controller_path))
			{
				// Lint?

				include $controller_path;

				$class_name = str_replace('_', null, ucwords($controller, '_')) . 'Controller';

				if(class_exists($class_name))
				{
					$controller = new $class_name($logger);

					if($controller instanceof ControllerBase)
					{
						$response = $controller->handleRequest($_SERVER['REQUEST_METHOD'], $route_info[2]);

						if(null === $response)
							return true;

						return $this->sendResponse($response);
					}

					$this->logger->critical('[SimpleFramework::handleRequest] Controller must extend ControllerBase',
					[
						'controller'	=> $controller,
					]);
					return $this->sendResponse(500);
				}

				$this->logger->critical('[SimpleFramework::handleRequest] Controller class not exist',
				[
					'controller'	=> $controller,
				]);
				return $this->sendResponse(500);
			}

			$this->logger->critical('[SimpleFramework::handleRequest] Controller file not readable',
			[
				'controller'	=> $controller,
			]);
			return $this->sendResponse(500);
		}

		private function sendResponse(int $http_response_code) : bool
		{
			switch($http_response_code)
			{
				case 404:
					http_response_code(404); // 404 Not Found
					echo '404';
					//Load template

					return true;

				case 405:
					http_response_code(405); // 405 Method Not Allowed

					if(!headers_sent())
						header('Allow: ' . implode(', ', $route_info[1]));

					echo '405';

					return true;

				case 500:
					http_response_code(500); // 500 Internal Server Error
					echo '500';

					return true;

				case 200:
					http_response_code(200); // 200 OK
					return true;

				// default:
			}

			return false;
		}
	}