<?php
	class AdminController extends ApplicationController
	{
		protected function init(string $http_method, array $environment)
		{
			$r = parent::init($http_method, $environment);

			if(!$this->userIsLoggedIn())
				return '/';

			return $r;
		}
	}