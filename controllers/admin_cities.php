<?php
	class AdminCitiesController extends AdminController
	{
		public function get(int $province_id, $search = null)
		{
			$province = (Province::find_all_by_id($province_id))[0] ?? null;

			if(!empty($province))
			{
				if(null === $search)
					$cities = City::all(
					[
						'conditions'	=> ['province_id = ?', $province_id],
						'order'			=> 'name ASC'
					]);
				else
					$cities = City::all(
					[
						'conditions'	=> ['province_id = ? AND name LIKE ?', $province_id, '%' . $search . '%'],
						'order'			=> 'name ASC',
					]);

				return
				[
					'provinces'	=> Province::all(['order' => 'name ASC']),
					'province'	=> $province,
					'cities'	=> $cities,
					'search'	=> $search
				];
			}

			return '/admin/places';
		}

		public function post($province_id)
		{
			if(!empty($_POST['id']) && !empty($_POST['province']) && !empty($_POST['name']))
			{
				// Create
				if('-' === $_POST['id'])
				{
					if(255 < strlen($_POST['name']))
						return "/admin/places/{$_POST['province']}?error=max_length&col=name&pcol=nombre&val=" . urlencode($_POST['name']);

					$province = (Province::find_all_by_id($_POST['province']))[0] ?? null;

					if(!empty($province))
					{
						$city = new City;

						$city->province_id = (int) $_POST['province'];
						$city->name = $_POST['name'];

						if($city->save())
							return "/admin/places/{$_POST['province']}?success=inserted#{$city->id}";

						return "/admin/places/{$_POST['province']}?error=unique&col=name&pcol=nombre&val=" . urlencode($_POST['name']);
					}
				}
				// Edit
				else
				{
					$city = (City::find_all_by_id($_POST['id']))[0] ?? null;
					$province = (Province::find_all_by_id($_POST['province']))[0] ?? null;

					if(!empty($city) && !empty($province))
					{
						$city->province_id = (int) $_POST['province'];
						$city->name = $_POST['name'];

						if($city->save())
							return "/admin/places/{$_POST['province']}?success=updated#{$_POST['id']}";

						return "/admin/places/{$province_id}#{$_POST['id']}";
					}
				}
			}
			// Delete
			else if(!empty($_POST['delete_id']))
			{
				$city = (City::find_all_by_id($_POST['delete_id']))[0] ?? null;

				if(!empty($city))
				{
					$city->delete();

					return "/admin/places/{$province_id}?success=deleted";
				}
			}
			// Search
			else if(!empty($_POST['search']))
				return "/admin/places/{$province_id}/" . urlencode($_POST['search']);

			// Anything else :P
			return "/admin/places/{$province_id}";
		}
	}