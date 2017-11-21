<?php
	class AdminKnowledgeAreasController extends AdminController
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
				$current = (KnowledgeArea::find_all_by_id($current_id))[0] ?? null;

				if(empty($current))
					return '/admin/k';
			}

			if(empty($current))
				$this->title = 'Áreas de conocimiento | Administración';
			else
				$this->title = "Áreas de conocimeinto en {$current->name} | Administración";

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

			$search_list = KnowledgeArea::all(['conditions' => $conditions, 'order' => 'name ASC']);

			// La lista completa con sus parents
			$list = KnowledgeArea::getList();

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
					else if(!empty($_POST['description']) && 255 < strlen($_POST['description']))
						$r = '?error=max_length&col=description&pcol=' . urlencode('descripción'). '&val=' . urlencode($_POST['description']);

					if(!empty($r))
					{
						if(empty($_POST['parent']))
							return "/admin/k{$r}";

						return "/admin/k/{$_POST['parent']}{$r}";
					}

					// Creamos el ítem
					$item = new KnowledgeArea;

					if(!empty($_POST['parent']) && is_numeric($_POST['parent']))
					{
						$parent = (KnowledgeArea::find_all_by_id($_POST['parent']))[0] ?? null;

						if(empty($parent))
							return '/admin/k';

						$item->parent_id = (int) $_POST['parent'];
					}

					$item->name = $_POST['name'];

					if(!empty($_POST['description']))
						$item->description = $_POST['description'];

					if($item->save())
						return "/admin/k/{$item->id}?success=inserted";

					$r = '?error=unique&col=name&pcol=nombre&val=' . urlencode($_POST['name']);

					if(empty($parent))
						return "/admin/k{$r}";

					return "/admin/k/{$_POST['parent']}{$r}";
				}
				// Editar
				else if(is_numeric($_POST['id']))
				{
					$item = (KnowledgeArea::find_all_by_id($_POST['id']))[0] ?? null;

					if(!empty($item))
					{
						if(!empty($_POST['parent']) && is_numeric($_POST['parent']))
						{
							$parent = (KnowledgeArea::find_all_by_id($_POST['parent']))[0] ?? null;

							if(empty($parent))
								return '/admin/k';

							$item->parent_id = (int) $_POST['parent'];
						}
						else
							$item->parent_id = null;

						if(255 < strlen($_POST['name']))
							$r = '?error=max_length&col=name&pcol=nombre&val=' . urlencode($_POST['name']);
						else if(!empty($_POST['description']) && 255 < strlen($_POST['description']))
							$r = '?error=max_length&col=description&pcol=' . urlencode('descripción'). '&val=' . urlencode($_POST['description']);

						if(!empty($r))
						{
							if(empty($_POST['parent']))
								return "/admin/k{$r}";

							return "/admin/k/{$_POST['parent']}{$r}";
						}

						$item->name = $_POST['name'];

						$item->description = $_POST['description'] ?? null;

						if($item->parent_id === $item->id)
							return '/admin/k?error=redundant';

						if($item->save())
						{
							if(empty($parent))
								return "/admin/k?success=updated#{$item->id}";

							return "/admin/k/{$item->parent_id}?success=updated#{$item->id}";
						}

						$r = '?error=unique&col=name&pcol=nombre&val=' . urlencode($_POST['name']);

						if(empty($parent))
							return "/admin/k{$r}";

						return "/admin/k/{$_POST['parent']}{$r}";
					}
				}
			}
			// Eliminar
			else if(!empty($_POST['delete_id']))
			{
				$item = (KnowledgeArea::find_all_by_id($_POST['delete_id']))[0] ?? null;

				if(!empty($item))
				{
					$parent_id = $item->parent_id;

					$item->delete();

					if(!empty($parent_id))
						return "/admin/k/{$parent_id}?success=deleted";

					return '/admin/k?success=deleted';
				}
			}
			// Búsqueda
			else if(!empty($_POST['search']))
			{
				$_POST['search'] = urlencode($_POST['search']);

				if(!empty($_POST['search_id']))
					return "/admin/k/{$_POST['search_id']}/{$_POST['search']}";

				return "/admin/k/{$_POST['search']}";
			}
			else if(!empty($_POST['search_id']))
				return "/admin/k/{$_POST['search_id']}";

			// En cualquier otro caso :P
			return '/admin/k';
		}
	}