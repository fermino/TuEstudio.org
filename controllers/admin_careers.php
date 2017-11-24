<?php
	class AdminCareersController extends AdminController
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
				$current = (University::find_all_by_id($current_id))[0] ?? null;

				if(empty($current))
					return '/admin/c';
			}

			if(empty($current))
				$this->title = 'Carreras | Administración';
			else
				$this->title = "Carreras en {$current->name} | Administración";

			$conditions = [''];

			if(!empty($current))
			{
				$conditions[0] .= 'university_id = ?';
				$conditions[] = $current->id;
			}

			if(!empty($search))
			{
				if(!empty($conditions[0]))
					$conditions[0] .= ' AND ';

				$conditions[0] .= 'name LIKE ?';
				$conditions[] = '%' . $search . '%';
			}

			$search_list = Career::all(['conditions' => $conditions, 'order' => 'name ASC']);

			// La lista completa con sus parents
			$p_list = Place::getList();
			$a_list = KnowledgeArea::getList();
			$u_list = University::getList();

			// Devolvemos los datos actuales
			return
			[
				'current_id'		=> $current->id ?? null,
				'current'			=> $current ?? null,
				'search_list'		=> $search_list,
				'p_list'			=> $p_list,
				'a_list'			=> $a_list,
				'u_list'			=> $u_list,
				'search'			=> $search
			];
		}

		public function post()
		{
			if(!empty($_POST['id']) && !empty($_POST['university']) && !empty($_POST['knowledge-area']) && !empty($_POST['name']) && !empty($_POST['description']) && isset($_POST['verified']))
			{
				// Crear
				if('-' === $_POST['id'])
				{
					if(255 < strlen($_POST['name']))
						$r = '?error=max_length&col=name&pcol=nombre&val=' . urlencode($_POST['name']);
					else if(!empty($_POST['degree']) && 255 < strlen($_POST['degree']))
						$r = '?error=max_length&col=degree&pcol=' . urlencode('título') . '&val=' . urlencode($_POST['degree']);

					if(!empty($r))
						return "/admin/c/{$_POST['university']}{$r}";

					$university = (University::find_all_by_id($_POST['university']))[0] ?? null;
					$knowledge_area = (KnowledgeArea::find_all_by_id($_POST['knowledge-area']))[0] ?? null;

					if(empty($university) || empty($knowledge_area))
						return '/admin/c';

					// Creamos el ítem
					$item = new Career;

					$item->university_id = $university->id;
					$item->knowledge_area_id = $knowledge_area->id;

					$item->name = $_POST['name'];

					$item->length = !empty($_POST['length']) ? $_POST['length'] : null;
					$item->description = $_POST['description'];

					$item->degree = !empty($_POST['degree']) ? $_POST['degree'] : null;

					$item->verified = (0 == $_POST['verified']) ? 0 : 1;

					if($item->save())
					{
						$cp = new CareerPlace();

						$cp->career_id = $item->id;
						$cp->place_id = $item->university->place_id;

						// Va a ser tomado como referencia a la dirección de la universidad (si cp->place_id coincide con u->place_id)
						$cp->address = null;

						$cp->save();

						return "/admin/c/{$university->id}?success=inserted#{$itme->id}";
					}

					return "/admin/c/{$university->id}?error=unique&col=name&pcol=nombre&val=" . urlencode($_POST['name']);
				}
				// Editar
				else if(is_numeric($_POST['id']))
				{
					$item = (Career::find_all_by_id($_POST['id']))[0] ?? null;

					if(!empty($item))
					{
						if(255 < strlen($_POST['name']))
							$r = '?error=max_length&col=name&pcol=nombre&val=' . urlencode($_POST['name']);
						else if(!empty($_POST['degree']) && 255 < strlen($_POST['degree']))
							$r = '?error=max_length&col=degree&pcol=' . urlencode('título') . '&val=' . urlencode($_POST['degree']);

						if(!empty($r))
							return "/admin/c/{$_POST['university']}{$r}";

						$university = (University::find_all_by_id($_POST['university']))[0] ?? null;
						$knowledge_area = (KnowledgeArea::find_all_by_id($_POST['knowledge-area']))[0] ?? null;

						if(empty($university) || empty($knowledge_area))
							return '/admin/c';

						$item->university_id = $university->id;
						$item->knowledge_area_id = $knowledge_area->id;

						$item->name = $_POST['name'];

						$item->length = !empty($_POST['length']) ? $_POST['length'] : null;
						$item->description = $_POST['description'];

						$item->degree = !empty($_POST['degree']) ? $_POST['degree'] : null;

						$item->verified = (0 == $_POST['verified']) ? 0 : 1;

						if($item->save())
							return "/admin/c/{$university->id}?success=updated#{$itme->id}";

						return "/admin/c/{$university->id}?error=unique&col=name&pcol=nombre&val=" . urlencode($_POST['name']);
					}
				}
			}
			// Eliminar
			else if(!empty($_POST['delete_id']))
			{
				$item = (Career::find_all_by_id($_POST['delete_id']))[0] ?? null;

				if(!empty($item))
				{
					$university_id = $item->university->id;

					foreach(CareerPlace::find_all_by_career_id($_POST['delete_id']) as $cp)
						$cp->delete();

					$item->delete();

					return "/admin/c/{$university_id}?success=deleted";
				}
			}
			// Búsqueda
			else if(!empty($_POST['search']))
			{
				$_POST['search'] = urlencode($_POST['search']);

				if(!empty($_POST['search_id']))
					return "/admin/c/{$_POST['search_id']}/{$_POST['search']}";

				return "/admin/c/{$_POST['search']}";
			}

			// En cualquier otro caso :P
			return '/admin/c';
		}
	}