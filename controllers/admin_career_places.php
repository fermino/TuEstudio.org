<?php
	class AdminCareerPlacesController extends AdminController
	{
		public function get($career_id)
		{
			$current = (Career::find_all_by_id($career_id))[0] ?? null;
			if(empty($current))
				return '/admin/c';

			$places = CareerPlace::find_all_by_career_id($career_id);

			$p_list = Place::getList();
			$u_list = University::getList();

			$u_name = implode(' > ', $u_list[$current->university->id]);
			$this->title = "{$current->name} en {$u_name} | Administración";

			return
			[
				'current'		=> $current,
				'current_place'	=> $current->university->place,
				'search_list'	=> $places,
				'p_list'		=> $p_list,
				'u_list'		=> $u_list
			];
		}

		public function post($career_id)
		{
			if(!empty($_POST['id']) && !empty($_POST['place']))
			{
				// Crear
				if('-' === $_POST['id'])
				{
					$career = (Career::find_all_by_id($career_id))[0] ?? null;
					if(empty($career))
						return '/admin/c';

					$place = (Place::find_all_by_id($_POST['place']))[0] ?? null;
					if(empty($place))
						return '/admin/c';

					$item = new CareerPlace;

					$item->career_id = (int) $career_id;
					$item->place_id = (int) $_POST['place'];

					$item->address = !empty($_POST['address']) ? $_POST['address'] : null;

					$item->save();

					return "/admin/cp/{$career_id}?success=inserted#{$item->id}";
				}
				// Editar
				else if(is_numeric($_POST['id']))
				{
					$item = (CareerPlace::find_all_by_id($_POST['id']))[0] ?? null;

					if(!empty($item))
					{
						$career = (Career::find_all_by_id($career_id))[0] ?? null;
						if(empty($career))
							return '/admin/c';

						$place = (Place::find_all_by_id($_POST['place']))[0] ?? null;
						if(empty($place))
							return '/admin/c';

						$item->career_id = (int) $career_id;
						$item->place_id = (int) $_POST['place'];

						$item->address = !empty($_POST['address']) ? $_POST['address'] : null;

						$item->save();

						return "/admin/cp/{$career_id}?success=updated#{$item->id}";
					}
				}
			}
			// Eliminar
			else if(!empty($_POST['delete_id']))
			{
				$item = (CareerPlace::find_all_by_id($_POST['delete_id']))[0] ?? null;

				if(!empty($item))
				{
					$item->delete();

					return "/admin/cp/{$career_id}?success=deleted";
				}
			}

			// En cualquier otro caso :P
			return '/admin/c';
		}
	}