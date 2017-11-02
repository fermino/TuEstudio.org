<?php
	class AdminProvincesController extends ApplicationController
	{
		protected $title = 'Provincias';

		public function get($search = null)
		{
			if(null === $search)
				$provinces = Province::all();
			else
				$provinces = Province::all(
				[
					'conditions'	=> ['name LIKE ?', '%' . $search . '%'],
					'order'			=> ['name DESC']
				]);

			return ['provinces' => $provinces, 'search' => $search];
		}

		public function post()
		{
			if(!empty($_POST['search']))
				return '/admin/provinces/' . urlencode($_POST['search']);
			else
				return '/admin/provinces';
		}
	}