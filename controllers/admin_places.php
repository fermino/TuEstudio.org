<?php
	class AdminPlacesController extends AdminController
	{
		public function get($id_or_search = null, $search = null)
		{
			$current_id = null;

			if(is_numeric($id_or_search))
				$current_id = (int) $id_or_search;
			else
				if(!empty($id_or_search))
					$search = $id_or_search;

			if(!empty($current_id))
			{
				$current = (Place::find_all_by_id($current_id))[0] ?? null;

				if(empty($current))
					return '/admin/p';
			}

			if(empty($current))
				$this->title = 'Lugares | Administración';
			else
				$this->title = "Lugares en {$current->name} | Administración";

			$conditions = [''];

			if(!empty($current))
			{
				$conditions[0] .= 'parent_id = ?';
				$conditions[] = $current->id;
			}
			else
				$conditions[0] .= 'parent_id IS NULL';

			if(!empty($search))
			{
				$conditions[0] .= ' AND name LIKE ?';
				$conditions[] = '%' . $search . '%';
			}

			$search_list = Place::all(['conditions' => $conditions, 'order' => 'name ASC']);

			// La lista completa con sus parents
			$list = Place::getList();

			// Devolvemos los datos actuales
			return
			[
				'current_id'	=> !empty($current) ? $current->id : null,
				'current'		=> $current ?? null,
				'search_list'	=> $search_list,
				'list'			=> $list,
				'search'		=> $search
			];
		}

		public function post()
		{
			if(!empty($_POST['id']) && !empty($_POST['name']))
			{
				// Crear
				if('-' === $_POST['id'])
				{
					if(255 < strlen($_POST['name']))
						$r = '?error=max_length&col=name&pcol=nombre&val=' . urlencode($_POST['name']);

					if(!empty($r))
					{
						if(empty($_POST['parent']))
							return "/admin/p{$r}";

						return "/admin/p/{$_POST['parent']}{$r}";
					}

					// Creamos el ítem
					$item = new Place;

					if(!empty($_POST['parent']) && is_numeric($_POST['parent']))
					{
						$parent = (Place::find_all_by_id($_POST['parent']))[0] ?? null;

						if(empty($parent))
							return '/admin/p';

						$item->parent_id = (int) $_POST['parent'];
					}

					$item->name = $_POST['name'];

					if($item->save())
						return "/admin/p/{$item->id}?success=inserted";

					if(empty($parent))
						return '/admin/p?error=unique&col=name&pcol=nombre&val=' . urlencode($_POST['name']);

					return "/admin/p/{$_POST['parent']}?error=unique&col=name&pcol=nombre&val=" . urlencode($_POST['name']);
				}
				// Editar
				else if(is_numeric($_POST['id']))
				{
					$item = (Place::find_all_by_id($_POST['id']))[0] ?? null;

					if(!empty($item))
					{
						if(!empty($_POST['parent']) && is_numeric($_POST['parent']))
						{
							$parent = (Place::find_all_by_id($_POST['parent']))[0] ?? null;

							if(empty($parent))
								return '/admin/p';

							$item->parent_id = (int) $_POST['parent'];
						}

						$item->name = $_POST['name'];

						if($item->save())
						{
							if(empty($parent))
								return "/admin/p?success=updated#{$item->id}";

							return "/admin/p/{$item->parent_id}?success=updated#{$item->id}";
						}

						$r = '?error=unique&col=name&pcol=nombre&val=' . urlencode($_POST['name']);

						if(empty($parent))
							return "/admin/p{$r}";

						return "/admin/p/{$_POST['parent']}{$r}";
					}
				}
			}
			// Eliminar
			else if(!empty($_POST['delete_id']))
			{
				$item = (Place::find_all_by_id($_POST['delete_id']))[0] ?? null;

				if(!empty($item))
				{
					$parent_id = $item->parent_id;

					$item->delete();

					if(!empty($parent_id))
						return "/admin/p/{$parent_id}?success=deleted";

					return '/admin/p?success=deleted';
				}
			}
			// Búsqueda
			else if(!empty($_POST['search']))
			{
				if(!empty($_POST['search_id']))
					return "/admin/p/{$_POST['search_id']}/{$_POST['search']}";

				return "/admin/p/{$_POST['search']}";
			}
			else if(!empty($_POST['search_id']))
				return "/admin/p/{$_POST['search_id']}";

			// En cualquier otro caso :P
			return '/admin/p';
		}
	}