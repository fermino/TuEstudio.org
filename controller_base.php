<?php
	require __DIR__.'/view_engine.php';

	use Psr\Log\LoggerInterface;

	abstract class ControllerBase
	{
		protected $unique_view = true;
		protected $title = null;

		protected $logger = null;

		final public function __construct(LoggerInterface $logger)
		{ $this->logger = $logger; }

		final public function handleRequest(string $http_method, array $environment) : ?int
		{
			if(method_exists($this, strtolower($http_method)))
			{
				$init_response = $this->init($http_method, $environment);
				if(is_int($init_response))
					return $init_response;

				$response = $this->{$http_method}(...array_values($environment));
				if(is_int($response))
					return $response;

				$this->terminate();

				if($this->unique_view)
				{
					if(is_array($response))
						$environment = $response;

					if(is_array($init_response))
						$environment = array_merge($init_response, $environment);

					$view_name = get_class($this);
					$view_name = substr($view_name, 0, strlen($view_name) - 10);

					$view_name[0] = strtolower($view_name[0]);
					$view_name = preg_replace_callback('/([A-Z])/', function($c) { return strtolower($c[1]); }, $view_name);

					if($this->loadControllerView($view_name, $environment))
						return null;

					$this->logger->critical('[Controller::handleRequest] Controller::loadControllerView() returned false',
					[
						'controller'		=> get_class($this),
						'view'				=> 'application',
						'controller_view'	=> $view_name,
						'method'			=> $http_method,
						'environment'		=> $environment
					]);
					return HTTP_INTERNAL_SERVER_ERROR;
				}

				return null;
			}

			$this->logger->critical('[Controller::handleRequest] Controller method does not exist',
			[
				'controller'	=> get_class($this),
				'method'		=> $http_method,
				'environment'	=> $environment
			]);
			return HTTP_INTERNAL_SERVER_ERROR;
		}

		final protected function loadControllerView(string $view_name, array $environment) : bool
		{
			$view = ViewEngine::loadView('application', $this->logger);

			if(!empty($view))
			{
				if($view->parse())
				{
					$environment = array_merge(['controller_view' => $view_name, 'title' => $this->title ?? null], $environment);

					if($view->display($environment))
						return true;

					$this->logger->critical('[Controller::loadControllerView] ViewEngine::display() returned false',
					[
						'controller'		=> get_class($this),
						'view'				=> 'application',
						'controller_view'	=> $view_name,
						'environment'		=> $environment
					]);
					return false;
				}

				$this->logger->critical('[Controller::loadControllerView] ViewEngine::parse() returned false',
				[
					'controller'		=> get_class($this),
					'view'				=> 'application',
					'controller_view'	=> $view_name,
					'environment'		=> $environment
				]);
				return false;
			}

			$this->logger->critical('[Controller::loadControllerView] ViewEngine::loadView() returned false: view not loadable',
			[
				'controller'		=> get_class($this),
				'view'				=> 'application',
				'controller_view'	=> $view_name,
				'environment'		=> $environment
			]);
			return false;
		}

		abstract protected function init(string $http_method, array $environment);
		abstract protected function terminate();
	}