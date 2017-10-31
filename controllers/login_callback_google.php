<?php
	class LoginCallbackGoogleController extends ApplicationController
	{
		public function get()
		{
			if(!$this->userIsLoggedIn())
			{
				try
				{
					if(!empty($_GET['code']))
					{
						$_SESSION['google_access_token'] = $this->google_client->fetchAccessTokenWithAuthCode($_GET['code']);

						$o_auth = new Google_Service_Oauth2($this->google_client);
						$user_info = $o_auth->userinfo_v2_me->get();

						$user = User::find_by_email($user_info->email);

						if(null === $user)
						{
							$user = new User;

							$user->email			= $user_info->email;
							$user->first_name		= $user_info->givenName;
							$user->last_name		= $user_info->familyName;
							$user->gender			= $user_info->gender[0]; // The first char

							$user->google_profile_id		= $user_info->id;
							$user->google_profile_picture	= $user_info->picture;

							if(false !== $user->save())
							{
								$_SESSION['remote_address'] = $_SERVER['REMOTE_ADDR'];
								$_SESSION['email'] = $user->email;

								return '/';
							}

							$this->logger->critical('[LoginCallbackGoogleController::get] the user could not be created',
							[
								'email'		=> $user_info->email,
								'user_info'	=> $user_info
							]);
							return 500;
						}

						$_SESSION['remote_address'] = $_SERVER['REMOTE_ADDR'];
						$_SESSION['email'] = $user->email;

						return '/';
					}
					
					if(!empty($_GET['error']))
						return '/';

					return HTTP_FORBIDDEN;
				}
				catch(Google_Service_Exception $e)
				{
					return HTTP_FORBIDDEN;
				}
			}

			return '/';
		}
	}