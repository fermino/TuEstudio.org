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
			'/places'		=>
			[
				''				=> ['admin_provinces', ['get', 'post']],
				'/{id:\d+}'		=>
				[
					''			=> ['admin_cities', ['get', 'post']],
					'/{search}'	=> ['admin_cities']
				],
				'/{search}'		=> 'admin_provinces',
			],
			'/knowledge-areas'	=>
			[
				''								=> ['admin_knowledge_areas', ['get', 'post']],
				'/{id_or_search}[/{search}]'	=> 'admin_knowledge_areas'
			]
		]
	];