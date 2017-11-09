<?php
	class AdminKnowledgeAreasController extends AdminController
	{
		public function get($id_or_search = null, $search = null)
		{
			if(is_numeric($id_or_search))
				$id = (int) $id_or_search;
			else
				if(!empty($id_or_search))
					$search = $id_or_search;

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
				'areas'			=> $current_areas,
				'all_areas'		=> $areas,
				'id'			=> $id ?? null,
				'search'		=> $search,
			];
		}

		public function post()
		{
			if(!empty($_POST['search']))
			{
				if(!empty($_POST['search_id']))
					return "/admin/knowledge-areas/{$_POST['search_id']}/{$_POST['search']}";

				return "/admin/knowledge-areas/{$_POST['search']}";
			}

			return "/admin/knowledge-areas";
		}
	}