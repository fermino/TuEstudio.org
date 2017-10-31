<?php
	require __DIR__.'/../controller_base.php';

	abstract class ApplicationController extends ControllerBase
	{
		protected $google_client = null;

		private $environment = [];

		protected function init(string $http_method, array $environment)
		{
			$r = $this->startSession();
			if(is_int($r))
				return $r;

			$r = $this->initGoogleClient();
			if(is_int($r))
				return $r;

			return $this->environment;
		}

		private function startSession() : ?int
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

				$this->logger->critical('[ApplicationController::startSession] The session can not be started',
				[
					'controller'	=> get_class($this)
				]);
				return 500;
			}

			return null;
		}

		protected function destroySession()
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

		private function initGoogleClient() : ?int
		{
			$json = file_get_contents(__DIR__.'/../config/google.json');

			if(false !== $json)
			{
				$json = json_decode($json);

				if(null !== $json)
				{
					$this->google_client = new Google_Client();

					$this->google_client->setClientId($json->id);
					$this->google_client->setClientSecret($json->secret);

					$this->google_client->setApplicationName('TuEstudio.org');
					$this->google_client->setRedirectUri('https://localhost/login-callback-google');

					$this->google_client->addScope('https://www.googleapis.com/auth/plus.login https://www.googleapis.com/auth/plus.me https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile');

					$this->environment['auth_url'] = $this->google_client->createAuthUrl();

					return null;
				}

				$this->logger->critical('[ApplicationController::initGoogleClient] config/google.json is not a json-valid file',
				[
					'controller'	=> get_class($this),
					'method'		=> $http_method
				]);
				return 500;
			}

			$this->logger->critical('[ApplicationController::initGoogleClient] config/google.json is not readable',
			[
				'controller'	=> get_class($this),
				'method'		=> $http_method
			]);
			return 500;
		}

		protected function terminate()
		{
			// Secure-Session is already doing this
			// session_write_close();
		}
	}