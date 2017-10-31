<?php
	require __DIR__.'/../controller_base.php';
	require __DIR__.'/../helpers/application.php';

	abstract class ApplicationController extends ControllerBase
	{
		use ApplicationHelper;

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

			$this->environment['user_logged_in'] = $this->userIsLoggedIn();

			return $this->environment;
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

		protected function userIsLoggedIn() : bool
		{
			return
			(
				!empty($_SESSION['remote_address']) &&
				$_SESSION['remote_address'] === $_SERVER['REMOTE_ADDR'] &&
				!empty($_SESSION['email'])
			);
		}

		protected function terminate()
		{ }
	}