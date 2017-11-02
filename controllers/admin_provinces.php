<?php
	class AdminProvincesController extends ApplicationController
	{
		protected $title = 'Provincias';

		public function get($search = null)
		{
			if(null === $search)
				$provinces = Province::all(
				[
					'order'		=> 'name ASC'
				]);
			else
				$provinces = Province::all(
				[
					'conditions'	=> ['name LIKE ?', '%' . $search . '%'],
					'order'			=> 'name ASC'
				]);

			return ['provinces' => $provinces, 'search' => $search];
		}

		public function post()
		{
			if(!empty($_POST['id']) && '-' === $_POST['id'] && !empty($_POST['name']))
			{
				$province = new Province;
				$province->name = $_POST['name'];

				if($province->save())
					return '/admin/provinces#' . $province->id;

				return '/admin/provinces';
			}
			else if(!empty($_POST['id']) && '-' !== $_POST['id'] && !empty($_POST['name']))
			{
				try
				{
					$province = Province::find($_POST['id']);

					if(null !== $province)
					{
						$province->name = $_POST['name'];

						if($province->save())
							return '/admin/provinces#' . $_POST['id'];
						else
							return '/admin/provinces?error=unique&col=name&pcol=nombre&val=' . urlencode($_POST['name']);
					}

					return '/admin/provinces#' . $_POST['id'];
				}
				catch(ActiveRecord\RecordNotFound $e)
				{
					return '/admin/provinces';
				}
			}
			else if(!empty($_POST['search']))
				return '/admin/provinces/' . urlencode($_POST['search']);
			else
				return '/admin/provinces';
		}
	}