<?php
	abstract class ControllerBase
	{
		private static $engines =
		[
			'PHP'
		];

		protected $unique_view = true;
		protected $view = null;

		final public function handleRequest(string $http_method, array $environment) : int
		{
			if(method_exists($this, strtolower($http_method)))
			{
				$response = $this->{$http_method}($environment);

				if(is_int($response) && 200 !== $response && 0 !== $response)
					return $response;

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
							if($this->view->display(is_array($response) ? $response : $environment))
								return 0;
							else
							{
								// Log
								return 500;
							}
						}
						else
						{
							// Log
							return 500;
						}
					}
					else
					{
						// Log
						return 500;
					}
				}
			}

			// Log error
			return 405;
		}

		final protected function loadView(string $view_name) : ?ViewEngine
		{
			foreach(self::$engines as $class_name)
			{
				require_once strtolower($class_name) . '_view.php';

				$class_name = $class_name . 'View';
				$view = new $class_name($view_name);

				if($view->isReadable())
					return $view;
			}

			return null;
		}
	}