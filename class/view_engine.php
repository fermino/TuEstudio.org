<?php
	abstract class ViewEngine
	{
		protected $view_name = null;

		final public function __construct(string $view_name)
		{
			if(!defined(get_class($this) . '::EXTENSION'))
				throw new Exception('You must define ' . get_class($this) . '::EXTENSION');

			$this->view_name = $view_name;
		}

		final protected function getPath() : string
		{ return realpath(__DIR__ . '/../views/' . $this->view_name . '.' . $this::EXTENSION); }

		final public function isReadable() : bool
		{ return is_readable($this->getPath()); }

		abstract public function parse() : bool;
		abstract public function display(array $environment) : bool;
	}