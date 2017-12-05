<?php
	class IndexController extends ApplicationController
	{
		public function get($search = null, $place_pretty_url = null, $knowledge_area_pretty_url = null)
		{
			if(!empty($knowledge_area_pretty_url))
			{
				$search = urldecode($search);
				$place_pretty_url = urldecode($place_pretty_url);
				$knowledge_area_pretty_url = urldecode($knowledge_area_pretty_url);

				if('-' !== $place_pretty_url)
				{
					$place = (Place::find_all_by_pretty_url($place_pretty_url))[0] ?? null;

					if(empty($place))
						return 404;

					$places = self::getChilds($place, 'places');
				}

				if('-' !== $knowledge_area_pretty_url)
				{
					$knowledge_area = (KnowledgeArea::find_all_by_pretty_url($knowledge_area_pretty_url))[0] ?? null;

					if(empty($knowledge_area))
						return 404;

					$knowledge_areas = self::getChilds($knowledge_area, 'areas');
				}

				if('-' === $search)
					$search = null;

				// University search

				$conditions = ['verified = 1'];

				if(!empty($search))
				{
					// Usar alguna vista (SQL) para poder manejar el score y ordenarlo

					$conditions[0] .= ' AND MATCH(name) AGAINST(?)';
					$conditions[] = $search;
				}

				if(!empty($places))
				{
					$conditions[0] .= ' AND place_id IN(?)';
					$conditions[] = $places;
				}

				$search_u_list = University::all(['conditions' => $conditions, 'order' => 'name ASC']);

				// Career search

				$conditions = ['verified = 1'];

				if(!empty($search))
				{
					// Usar alguna vista (SQL) para poder manejar el score y ordenarlo

					$conditions[0] .= ' AND MATCH(name, degree, middle_degree, description) AGAINST(?)';
					$conditions[] = $search;
				}

				if(!empty($places))
				{
					$places_careers = CareerPlace::all(['conditions' => ['place_id IN(?)', $places]]);
					$places_careers = array_map(function($item) { return $item->career_id; }, $places_careers);

					$conditions[0] .= ' AND id IN(?)';
					$conditions[] = $places_careers;
				}

				if(!empty($knowledge_areas))
				{
					$conditions[0] .= ' AND knowledge_area IN(?)';
					$conditions[] = $knowledge_areas;
				}

				$search_c_list = Career::all(['conditions' => $conditions, 'order' => 'name ASC']);
			}

			$places = Place::getList();
			$knowledge_areas = KnowledgeArea::getList();
			$universities = University::getList(true, true);

			if(!empty($search))
				$this->title = $search . ' | ';

			$this->title .= 'Encuentra universidades y carreras en todo el país';

			return
			[
				'search'				=> $search,
				'search_place'			=> $place->id ?? null,
				'search_knowledge_area'	=> $knowledge_area->id ?? null,
				'search_u_list'			=> $search_u_list ?? [],
				'search_c_list'			=> $search_c_list ?? [],
				'places'				=> $places,
				'knowledge_areas'		=> $knowledge_areas,
				'universities'			=> $universities
			];
		}

		public function post()
		{
			$search = !empty($_POST['search']) ? urlencode(str_replace('/', '-', $_POST['search'])) : '-';

			$place = ((Place::find_all_by_id($_POST['place'] ?? 0))[0])->pretty_url ?? '-';
			$knowledge_area = ((KnowledgeArea::find_all_by_id($_POST['knowledge_area'] ?? 0))[0])->pretty_url ?? '-';

			return "/search/{$search}/{$place}/{$knowledge_area}";
		}

		private static function getChilds($item, string $childs_name = 'childs') : array
		{
			$childs = [$item->id];

			foreach($item->{$childs_name} as $i)
				$childs = array_merge($childs, self::getChilds($i, $childs_name));

			return array_unique($childs);
		}
	}