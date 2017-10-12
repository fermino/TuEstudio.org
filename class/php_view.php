<?php
	class PHPView extends ViewEngine
	{
		const EXTENSION = 'php';

		public function parse() : bool
		{
			// Add linter?
			if($this->isReadable())
				return true;

			$this->logger->critical('[ViewEngine::parse] The view is not readable',
			[
				'engine'		=> get_class($this),
				'view'			=> $this->view_name,
				'path'			=> $this->path
			]);
			return false;
		}

		public function display(array $environment) : bool
		{
			extract($environment, EXTR_OVERWRITE);

			return include $this->path;
		}
	}