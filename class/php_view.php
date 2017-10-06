<?php
	require_once __DIR__ . '/view_engine.php';

	class PHPView extends ViewEngine
	{
		const EXTENSION = 'php';

		public function parse() : bool
		{
			// Add linter?
			if($this->isReadable())
				return true;
			// Log error
			// throw new Exception('The view ' . $this->view_name . ' is not readable');

			return false;
		}

		public function display(array $environment) : bool
		{
			extract($environment, EXTR_OVERWRITE);

			return include $this->getPath();
		}
	}