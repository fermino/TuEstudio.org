<?php
	$routes = 
	[
		'/'								=> 'index',
		'/assets/{folder}/{file}.{ext}'	=> 'assets',
		'/login-callback-google'		=> 'login_callback_google',
		'/logout'						=> 'logout',
		'/admin'						=>
		[
			''												=> ['admin_index'			,['get']		],
			'/p[/{id_or_search}[/{search}]]'				=> ['admin_places'			,['get', 'post']],
			'/k[/{id_or_search}[/{search}]]'				=> ['admin_knowledge_areas'	,['get', 'post']],
		],
	];