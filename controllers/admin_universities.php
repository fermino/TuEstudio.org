<?php
	class AdminUniversitiesController extends AdminController
	{
		public function get($place_id = null, $id_or_search = null, $search = null)
		{
			$current_id = null;

			if(is_numeric($id_or_search))
				$current_id = (int) $id_or_search;
			else
				if(!empty($id_or_search))
					$search = $id_or_search;

			if(!empty($current_id))
			{
				$current = (University::find_all_by_id($current_id))[0] ?? null;

				if(empty($current))
					return '/admin/u';
			}

			if(empty($current))
				$this->title = 'Universidades | Administración';
			else
				$this->title = "{$current->name} | Administración";

			$conditions = [''];

			if(!empty($current))
			{
				$conditions[0] .= 'parent_id = ?';
				$conditions[] = $current->id;
			}
			else if(!empty($place_id))
			{
				$conditions[0] .= 'place_id = ?';
				$conditions[] = $place_id;
			}
			else
				$conditions[0] .= 'parent_id IS NULL';

			if(!empty($search))
			{
				if(!empty($conditions[0]))
					$conditions[0] .= ' AND ';

				$conditions[0] .= 'name LIKE ?';
				$conditions[] = '%' . $search . '%';
			}

			$search_list = University::all(['conditions' => $conditions, 'order' => 'name ASC']);

			// La lista completa con sus parents
			$list = University::getList();
			$p_list	= Place::getList();

			// Devolvemos los datos actuales
			return
			[
				'current_id'		=> $current->id ?? null,
				'current'			=> $current ?? null,
				'current_place_id'	=> $current->place_id ?? null,
				'search_list'		=> $search_list,
				'list'				=> $list,
				'p_list'			=> $p_list,
				'place_id'			=> $place_id,
				'search'			=> $search
			];
		}

		public function post()
		{
			if(!empty($_POST['id']) && !empty($_POST['name']) && !empty($_POST['place']) && isset($_POST['verified']))
			{
				// Crear
				if('-' === $_POST['id'])
				{
					if(255 < strlen($_POST['name']))
						$r = '?error=max_length&col=name&pcol=nombre&val=' . urlencode($_POST['name']);
					//else if(!empty($_POST['description']) && 255 < strlen($_POST['description']))
					//	$r = '?error=max_length&col=description&pcol=' . urlencode('descripción'). '&val=' . urlencode($_POST['description']);
					else if(!empty($_POST['web']) && 255 < strlen($_POST['web']))
						$r = '?error=max_length&col=web&pcol=web&val=' . urlencode($_POST['web']);
					else if(!empty($_POST['email']) && 255 < strlen($_POST['email']))
						$r = '?error=max_length&col=email&pcol=e-mail&val=' . urlencode($_POST['email']);
					else if(!empty($_POST['phone']) && 255 < strlen($_POST['phone']))
						$r = '?error=max_length&col=phone&pcol=' . urlencode('teléfono'). '&val=' . urlencode($_POST['phone']);
					else if(!empty($_POST['address']) && 255 < strlen($_POST['address']))
						$r = '?error=max_length&col=address&pcol=' . urlencode('dirección'). '&val=' . urlencode($_POST['address']);

					if(!empty($r))
					{
						if(empty($_POST['parent']))
							return "/admin/u{$r}";

						return "/admin/u/-/{$_POST['parent']}{$r}";
					}

					$place = (Place::find_all_by_id($_POST['place']))[0] ?? null;

					if(empty($place))
						return '/admin/u';

					// Creamos el ítem
					$item = new University;

					$item->place_id = (int) $_POST['place'];

					if(!empty($_POST['parent']) && is_numeric($_POST['parent']))
					{
						$parent = (University::find_all_by_id($_POST['parent']))[0] ?? null;

						if(empty($parent))
							return '/admin/u';

						$item->parent_id = (int) $_POST['parent'];
					}

					$item->name = $_POST['name'];

					//if(!empty($_POST['description']))
					//	$item->description = $_POST['description'];
					if(!empty($_POST['web']))
						$item->web_address = $_POST['web'];
					if(!empty($_POST['email']))
						$item->email = $_POST['email'];
					if(!empty($_POST['phone']))
						$item->phone = $_POST['phone'];
					if(!empty($_POST['address']))
						$item->address = $_POST['address'];

					$item->verified = (0 == $_POST['verified']) ? 0 : 1;

					if($item->parent_id === $item->id)
						return '/admin/u?error=redundant';

					if($item->save())
						return "/admin/u/-/{$item->id}?success=inserted";

					$r = '?error=unique&col=name&pcol=nombre&val=' . urlencode($_POST['name']);

					if(empty($parent))
						return "/admin/u{$r}";

					return "/admin/u/-/{$_POST['parent']}{$r}";
				}
				// Editar
				else if(is_numeric($_POST['id']))
				{
					$item = (University::find_all_by_id($_POST['id']))[0] ?? null;

					if(!empty($item))
					{
						if(255 < strlen($_POST['name']))
							$r = '?error=max_length&col=name&pcol=nombre&val=' . urlencode($_POST['name']);
						//else if(!empty($_POST['description']) && 255 < strlen($_POST['description']))
						//	$r = '?error=max_length&col=description&pcol=' . urlencode('descripción'). '&val=' . urlencode($_POST['description']);
						else if(!empty($_POST['web']) && 255 < strlen($_POST['web']))
							$r = '?error=max_length&col=web&pcol=web&val=' . urlencode($_POST['web']);
						else if(!empty($_POST['email']) && 255 < strlen($_POST['email']))
							$r = '?error=max_length&col=email&pcol=e-mail&val=' . urlencode($_POST['email']);
						else if(!empty($_POST['phone']) && 255 < strlen($_POST['phone']))
							$r = '?error=max_length&col=phone&pcol=' . urlencode('teléfono'). '&val=' . urlencode($_POST['phone']);
						else if(!empty($_POST['address']) && 255 < strlen($_POST['address']))
							$r = '?error=max_length&col=address&pcol=' . urlencode('dirección'). '&val=' . urlencode($_POST['address']);

						if(!empty($r))
						{
							if(empty($_POST['parent']))
								return "/admin/u{$r}";

							return "/admin/u/-/{$_POST['parent']}{$r}";
						}

						$place = (Place::find_all_by_id($_POST['place']))[0] ?? null;

						if(empty($place))
							return '/admin/u';

						$item->place_id = (int) $_POST['place'];

						if(!empty($_POST['parent']) && is_numeric($_POST['parent']))
						{
							$parent = (University::find_all_by_id($_POST['parent']))[0] ?? null;

							if(empty($parent))
								return '/admin/u';

							$item->parent_id = (int) $_POST['parent'];
						}

						$item->name = $_POST['name'];

						//if(!empty($_POST['description']))
						//	$item->description = $_POST['description'];
						if(!empty($_POST['web']))
							$item->web_address = $_POST['web'];
						if(!empty($_POST['email']))
							$item->email = $_POST['email'];
						if(!empty($_POST['phone']))
							$item->phone = $_POST['phone'];
						if(!empty($_POST['address']))
							$item->address = $_POST['address'];

						$item->verified = (0 == $_POST['verified']) ? 0 : 1;

						if($item->parent_id === $item->id)
							return '/admin/u?error=redundant';

						if($item->save())
						{
							if(empty($parent))
								return "/admin/u?success=updated#{$item->id}";

							return "/admin/u/-/{$item->parent_id}?success=updated#{$item->id}";
						}

						$r = '?error=unique&col=name&pcol=nombre&val=' . urlencode($_POST['name']);

						if(empty($parent))
							return "/admin/u{$r}";

						return "/admin/u/-/{$_POST['parent']}{$r}";
					}
				}
			}
			// Eliminar
			else if(!empty($_POST['delete_id']))
			{
				$item = (University::find_all_by_id($_POST['delete_id']))[0] ?? null;

				if(!empty($item))
				{
					$parent_id = $item->parent_id;

					$item->delete();

					if(!empty($parent_id))
						return "/admin/u/-/{$parent_id}?success=deleted";

					return '/admin/u?success=deleted';
				}
			}
			// Búsqueda
			else if(!empty($_POST['search']))
			{
				$_POST['search'] = urlencode($_POST['search']);

				if(!empty($_POST['search_id']))
					$r = "{$_POST['search_id']}/{$_POST['search']}";
				else
					$r = $_POST['search'];

				if(!empty($_POST['search_place_id']))
					return "/admin/u/{$_POST['search_place_id']}/{$r}";

				return "/admin/u/0/{$r}";
			}
			else if(!empty($_POST['search_id']))
			{
				if(!empty($_POST['search_place_id']))
					return "/admin/u/{$_POST['search_place_id']}/{$_POST['search_id']}";

				return "/admin/u/0/{$_POST['search_id']}";
			}

			// En cualquier otro caso :P
			return '/admin/u';
		}
	}