<?php
	require __DIR__.'/../controller_base.php';

	abstract class ApplicationController extends ControllerBase
	{
		protected function init(string $http_method, array $environment) : ?int
		{
			return null;
		}
	}