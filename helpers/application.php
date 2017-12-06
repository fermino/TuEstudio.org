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

		// http://cubiq.org/the-perfect-php-clean-url-generator
		public static function getPrettyURL(string $url, array $replace = [], string $delimiter = '-') : string
		{
			if(!empty($replace))
				$url = str_replace($replace, ' ', $url);

			$url = iconv('UTF-8', 'ASCII//TRANSLIT', $url);
			$url = preg_replace('/[^a-zA-Z0-9\\/_|+ -]/', '', $url);
			$url = strtolower(trim($url, '-'));
			$url = preg_replace('/[\\/_|+ -]+/', $delimiter, $url);

			return $url;
		}

		// Nota: solo funca para argentos wacho
		public static function formatPhone(string $phone) : ?array
		{
			if(10 !== strlen($phone))
				return null;

			$prefixes = ['2202', '2221', '2223', '2224', '2225', '2226', '2227', '2229', '2241', '2242', '2243', '2244', '2245', '2246', '2252', '2254', '2255', '2257', '2261', '2262', '2264', '2265', '2266', '2267', '2268', '2271', '2272', '2273', '2274', '2281', '2283', '2284', '2285', '2286', '2291', '2292', '2296', '2297', '2302', '2314', '2316', '2317', '2320', '2323', '2324', '2325', '2326', '2331', '2333', '2334', '2335', '2336', '2337', '2338', '2342', '2343', '2344', '2345', '2346', '2352', '2353', '2354', '2355', '2356', '2357', '2358', '2392', '2393', '2394', '2395', '2396', '2473', '2474', '2475', '2477', '2478', '2622', '2624', '2625', '2626', '2646', '2647', '2648', '2651', '2652', '2655', '2656', '2657', '2658', '2901', '2902', '2903', '2920', '2921', '2922', '2923', '2924', '2925', '2926', '2927', '2928', '2929', '2931', '2932', '2933', '2934', '2935', '2936', '2940', '2942', '2945', '2946', '2948', '2952', '2953', '2954', '2962', '2963', '2964', '2966', '2972', '2982', '2983', '3327', '3329', '3382', '3385', '3387', '3388', '3400', '3401', '3402', '3404', '3405', '3406', '3407', '3408', '3409', '3435', '3436', '3437', '3438', '3442', '3444', '3445', '3446', '3447', '3454', '3455', '3456', '3458', '3460', '3462', '3463', '3464', '3465', '3466', '3467', '3468', '3469', '3471', '3472', '3476', '3482', '3483', '3487', '3489', '3491', '3492', '3493', '3496', '3497', '3498', '3521', '3522', '3524', '3525', '3532', '3533', '3537', '3541', '3542', '3543', '3544', '3546', '3547', '3548', '3549', '3562', '3563', '3564', '3571', '3572', '3573', '3574', '3575', '3576', '3582', '3583', '3584', '3585', '3711', '3715', '3716', '3718', '3721', '3725', '3731', '3734', '3735', '3741', '3743', '3751', '3754', '3755', '3756', '3757', '3758', '3772', '3773', '3774', '3775', '3777', '3781', '3782', '3786', '3821', '3825', '3826', '3827', '3832', '3835', '3837', '3838', '3841', '3843', '3844', '3845', '3846', '3854', '3855', '3856', '3857', '3858', '3861', '3862', '3863', '3865', '3867', '3868', '3869', '3873', '3876', '3877', '3878', '3885', '3886', '3887', '3888', '3891', '3892', '3894', '220', '221', '223', '230', '236', '237', '249', '260', '261', '263', '264', '266', '280', '291', '294', '297', '298', '299', '336', '341', '342', '343', '345', '348', '351', '353', '358', '362', '364', '370', '376', '379', '380', '381', '383', '385', '387', '388', '11'];

			$prefix_length = null;

			foreach($prefixes as $prefix)
			{
				if(substr($phone, 0, strlen($prefix)) == $prefix)
				{
					$prefix_length = strlen($prefix);

					break;
				}
			}

			switch($prefix_length)
			{
				case 2:
					return [substr($phone, 0, 2), substr($phone, 2, 4), substr($phone, 6, 4)];
				case 3:
					return [substr($phone, 0, 3), substr($phone, 3, 3), substr($phone, 6, 4)];
				case 4:
					return [substr($phone, 0, 4), substr($phone, 4, 2), substr($phone, 6, 4)];
			}

			return null;
		}
	}