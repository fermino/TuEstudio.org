<?php
	$routes = 
	[
		'/'								=> 'index',
		'/assets/{folder}/{file}.{ext}'	=> 'assets',
		'/login-callback-google'		=> 'login_callback_google',
		'/logout'						=> 'logout',
		'/admin'						=>
		[
			''									=> 'admin_index',
			'/p[/{id_or_search}[/{search}]]'	=> ['admin_places', ['get', 'post']],
			'/knowledge-areas'	=>
			[
				''								=> ['admin_knowledge_areas', ['get', 'post']],
				'/{id_or_search}[/{search}]'	=> 'admin_knowledge_areas'
			]
		]
	];