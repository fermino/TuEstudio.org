<?php
	class AdminKnowledgeAreasController extends AdminController
	{
		public function get($id_or_search = null, $search = null)
		{
			$id = null;

			if(is_numeric($id_or_search))
				$id = (int) $id_or_search;
			else
				if(!empty($id_or_search))
					$search = $id_or_search;

			$current_area = (KnowledgeArea::find_all_by_id($id))[0] ?? null;
			if(empty($current_area))
				$id = null;

			$conditions = [''];

			if(!empty($id))
			{
				$conditions[0] .= 'parent_id = ?';
				$conditions[] = $id;
			}
			else
				$conditions[0] .= 'parent_id IS NULL';

			if(!empty($search))
			{
				$conditions[0] .= ' AND name LIKE ?';
				$conditions[] = '%' . $search . '%';
			}

			$current_areas = KnowledgeArea::all(['conditions' => $conditions, 'order' => 'name ASC']);

			// The select dropdown

			$areas = [];
			foreach(KnowledgeArea::all() as $area)
			{
				$areas[$area->id] = [$area->id => $area->name];

				$parent = $area->parent;

				while(!empty($parent))
				{
					$areas[$area->id][$parent->id] = $parent->name;

					$parent = $parent->parent;
				}

				$areas[$area->id] = array_reverse($areas[$area->id], true);
			}

			uasort($areas, function($a, $b)
			{ return strnatcmp(implode('/', $a), implode('/', $b)); });

			return
			[
				'current_area'	=> $current_area,
				'areas'			=> $current_areas,
				'all_areas'		=> $areas,
				'id'			=> $id,
				'search'		=> $search,
			];
		}

		public function post()
		{
			if(!empty($_POST['id']) && !empty($_POST['parent']) && !empty($_POST['name']))
			{
				// Crear
				if('-' === $_POST['id'])
				{
					if(255 < strlen($_POST['name']))
					{
						$r = '?error=max_length&col=name&pcol=nombre&val=' . urlencode($_POST['name']);

						if('-' === $_POST['parent'])
							return "/admin/knowledge-areas{$r}";

						return "/admin/knowledge-areas/{$_POST['parent']}{$r}";
					}

					if(255 < strlen($_POST['description']))
					{
						$r = '?error=max_length&col=description&pcol=' . urlencode('descripción'). '&val=' . urlencode($_POST['description']);

						if('-' === $_POST['parent'])
							return "/admin/knowledge-areas{$r}";
						
						return "/admin/knowledge-areas/{$_POST['parent']}{$r}";
					}

					// Creamos el área
					$area = new KnowledgeArea;

					if(is_numeric($_POST['parent']))
					{
						$parent = (KnowledgeArea::find_all_by_id($_POST['parent']))[0] ?? null;

						if(empty($parent))
							return '/admin/knowledge-areas';

						$area->parent_id = (int) $_POST['parent'];
					}

					$area->name = $_POST['name'];

					if(!empty($_POST['description']))
						$area->description = $_POST['description'];

					if($area->save())
						return "/admin/knowledge-areas/{$area->id}?success=inserted";

					if(empty($parent))
						return '/admin/knowledge-areas?error=unique&col=name&pcol=nombre&val=' . urlencode($_POST['name']);

					return "/admin/knowledge-areas/{$_POST['parent']}?error=unique&col=name&pcol=nombre&val=" . urlencode($_POST['name']);
				}
			}
			// Búsqueda
			else if(!empty($_POST['search']))
			{
				if(!empty($_POST['search_id']))
					return "/admin/knowledge-areas/{$_POST['search_id']}/{$_POST['search']}";

				return "/admin/knowledge-areas/{$_POST['search']}";
			}

			// En cualquier otro caso :P
			return "/admin/knowledge-areas";
		}
	}