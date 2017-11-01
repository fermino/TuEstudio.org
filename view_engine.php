<?php
	use Psr\Log\LoggerInterface;

	abstract class ViewEngine
	{
		protected $view_name	= null;
		protected $path			= null;

		protected $logger = null;

		final public function __construct(string $view_name, LoggerInterface $logger)
		{
			$this->logger = $logger;

			if(!defined(get_class($this) . '::EXTENSION'))
			{
				$this->logger->critical('[ViewEngine::__construct] ViewEngine::EXTENSION must be defined',
				[
					'engine'		=> get_class($this),
					'view'			=> $view_name
				]);

				throw new Exception('You must define ' . get_class($this) . '::EXTENSION');
			}

			$this->view_name = $view_name;
			$this->path = __DIR__.'/views/' . $this->view_name . '.' . $this::EXTENSION;
		}

		final public function isReadable() : bool
		{ return is_readable($this->path); }

		abstract public function parse() : bool;
		abstract public function display(array $environment = []) : bool;

		// Autoload (glob('view_*'))
		private static $engines =
		[
			'PHP',
			'Sli'
		];

		final static public function loadView(string $view_name, LoggerInterface $logger) : ?ViewEngine
		{
			foreach(self::$engines as $class_name)
			{
				require_once __DIR__.'/view_engines/' . strtolower($class_name) . '_view.php';

				// Check is_subclass_of

				$class_name = $class_name . 'View';
				$view = new $class_name($view_name, $logger);

				if($view->isReadable())
					return $view;
			}

			$logger->critical('[ViewEngine::loadView] No such view',
			[
				'view'	=> $view_name
			]);
			return null;
		}
	}