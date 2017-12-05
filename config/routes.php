<?php
	$routes = 
	[
		// Main page
		'/'											=> ['index', ['get', 'post']],
		'/search/{search}/{place}/{knowledge_area}'	=> 'index',
		// Assets
		'/assets/{folder}/{file}.{ext}'	=> 'assets',
		// User session
		'/login-callback-google'	=> 'login_callback_google',
		'/logout'					=> 'logout',
		// User
		'/u/{university_pretty_url}'	=> 'university',
		'/c/{career_pretty_url}'		=> 'career',
		// Admin
		'/admin'	=>
		[
			''												=> ['admin_index'			,['get']		],
			'/p[/{id_or_search}[/{search}]]'				=> ['admin_places'			,['get', 'post']],
			'/k[/{id_or_search}[/{search}]]'				=> ['admin_knowledge_areas'	,['get', 'post']],
			'/u[/{place_id}[/{id_or_search}[/{search}]]]'	=> ['admin_universities'	,['get', 'post']],
			'/c[/{university_id}[/{search}]]'				=> ['admin_careers'			,['get', 'post']],
			'/cp/{career_id:\d+}'							=> ['admin_career_places'	,['get', 'post']]
		],
	];