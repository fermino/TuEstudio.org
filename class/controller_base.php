<?php
	use Psr\Log\LoggerInterface;

	abstract class ControllerBase
	{
		private static $engines =
		[
			'PHP'
		];

		protected $unique_view = true;
		protected $view = null;

		protected $logger = null;

		final public function __construct(LoggerInterface $logger)
		{ $this->logger = $logger; }

		final public function handleRequest(string $http_method, array $environment) : ?int
		{
			if(method_exists($this, strtolower($http_method)))
			{
				$response = $this->{$http_method}($environment);

				if(is_int($response))
					return $response;

				if(is_array($response))
					$environment = $response;

				if($this->unique_view)
				{
					$view_name = get_class($this);
					$view_name = substr($view_name, 0, strlen($view_name) - 10);

					$view_name[0] = strtolower($view_name[0]);
					$view_name = preg_replace_callback('/([A-Z])/', function($c) { return strtolower($c[1]); }, $view_name);

					$this->view = $this->loadView($view_name);

					if(!empty($this->view))
					{
						if($this->view->parse())
						{
							if($this->view->display($environment))
								return null;

							$this->logger->critical('[Controller::handleRequest] ViewEngine::display() returned false',
							[
								'controller'	=> get_class($this),
								'view'			=> $view_name,
								'method'		=> $http_method,
								'environment'	=> $environment
							]);
							return 500;
						}

						$this->logger->critical('[Controller::handleRequest] ViewEngine::parse() returned false',
						[
							'controller'	=> get_class($this),
							'view'			=> $view_name,
							'method'		=> $http_method,
							'environment'	=> $environment
						]);
						return 500;
					}

					$this->logger->critical('[Controller::handleRequest] Controller::loadView() returned false: view not loadable',
					[
						'controller'	=> get_class($this),
						'view'			=> $view_name,
						'method'		=> $http_method,
						'environment'	=> $environment
					]);
					return 500;
				}

				return null;
			}

			$this->logger->critical('[Controller::handleRequest] Controller method does not exist',
			[
				'controller'	=> get_class($this),
				'method'		=> $http_method,
				'environment'	=> $environment
			]);
			return 500;
		}

		final protected function loadView(string $view_name) : ?ViewEngine
		{
			foreach(self::$engines as $class_name)
			{
				require_once strtolower($class_name) . '_view.php';

				$class_name = $class_name . 'View';
				$view = new $class_name($view_name, $this->logger);

				if($view->isReadable())
					return $view;
			}

			return null;
		}
	}