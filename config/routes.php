<?php
	$routes = 
	[
		'/'								=> 'index',
		'/assets/{folder}/{file}.{ext}'	=> 'assets',
		'/login-callback-google'		=> 'login_callback_google',
		'/logout'						=> 'logout',
		'/admin'						=>
		[
			''				=> 'admin_index',
			'/provinces'		=>
			[
				''				=> ['admin_provinces', ['get', 'post']],
				'/{search}'		=> 'admin_provinces'
			]
		]
	];