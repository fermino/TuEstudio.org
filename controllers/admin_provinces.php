<?php
	class AdminProvincesController extends AdminController
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
			if(!empty($_POST['delete_id']))
			{
				try
				{
					$province = Province::find($_POST['delete_id']);

					if(null !== $province)
					{
						$province->delete();

						return '/admin/places?success=deleted';
					}
				}
				catch(ActiveRecord\RecordNotFound $e)
				{ }

				return '/admin/places';
			}
			else if(!empty($_POST['id']) && '-' === $_POST['id'] && !empty($_POST['name']))
			{
				if(255 < strlen($_POST['name']))
					return '/admin/places?error=max_length&col=name&pcol=nombre&val=' . urlencode($_POST['name']);

				$province = new Province;
				$province->name = $_POST['name'];

				if($province->save())
					return '/admin/places?success=inserted#' . $province->id;

				return '/admin/places?error=unique&col=name&pcol=nombre&val=' . urlencode($_POST['name']);
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
							return '/admin/places?success=updated#' . $_POST['id'];
						else
							return '/admin/places?error=unique&col=name&pcol=nombre&val=' . urlencode($_POST['name']);
					}

					return '/admin/places#' . $_POST['id'];
				}
				catch(ActiveRecord\RecordNotFound $e)
				{
					return '/admin/places';
				}
			}
			else if(!empty($_POST['search']))
				return '/admin/places/' . urlencode($_POST['search']);
			else
				return '/admin/places';
		}
	}