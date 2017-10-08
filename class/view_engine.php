<?php
	use Psr\Log\LoggerInterface;

	abstract class ViewEngine
	{
		protected $view_name = null;

		protected $logger = null;

		final public function __construct(string $view_name, LoggerInterface $logger)
		{
			$this->logger = $logger;

			if(!defined(get_class($this) . '::EXTENSION'))
			{
				$this->logger->critical('[ViewEngine::__construct] ViewEngine::EXTENSION must be defined',
				[
					'engine'		=> get_class($this),
					'view'			=> $view_name,
				]);

				throw new Exception('You must define ' . get_class($this) . '::EXTENSION');
			}

			$this->view_name = $view_name;
		}

		final protected function getPath() : string
		{ return __DIR__.'/../views/' . $this->view_name . '.' . $this::EXTENSION; }

		final public function isReadable() : bool
		{ return is_readable($this->getPath()); }

		abstract public function parse() : bool;
		abstract public function display(array $environment) : bool;
	}