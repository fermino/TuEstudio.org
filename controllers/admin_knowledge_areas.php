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

			$areas = KnowledgeArea::all(['conditions' => $conditions, 'order' => 'name ASC']);

			return
			[
				'areas'		=> $areas,
				'id'		=> $id ?? null,
				'search'	=> $search,
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