<?php
	trait ApplicationHelper
	{
		protected function startSession() : ?int
		{
			ini_set('session.use_cookies', 1);
			ini_set('session.use_only_cookies', 1);
			session_save_path(__DIR__.'/../sessions/');

			session_set_cookie_params(0, ini_get('session.cookie_path'), ini_get('session.cookie_domain'), isset($_SERVER['HTTPS']), true);
			session_name('tuestudio');

			if('' === session_id())
			{
				if(session_start())
					return null;

				$this->logger->critical('[ApplicationHelper::startSession] The session can not be started',
				[
					'controller'	=> get_class($this)
				]);
				return 500;
			}

			return null;
		}

		protected function destroySession() : bool
		{
			if('' !== session_id())
			{
				$p = session_get_cookie_params();

				session_unset();
				setcookie('tuestudio', '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);

				return session_destroy();
			}

			return true;
		}
	}