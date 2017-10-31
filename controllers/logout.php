<?php
	class LogoutController extends ApplicationController
	{
		public function get()
		{
			$this->destroySession();

			return '/';
		}
	}