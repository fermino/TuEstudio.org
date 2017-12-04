<?php
	class UniversityController extends ApplicationController
	{
		public function get($university_pretty_url)
		{
			$current = (University::find_all_by_pretty_url_and_verified($university_pretty_url, true))[0] ?? null;

			if(empty($current))
				return '/';

			$parents = $current->getParentList(true, true);
			$root_pretty_url = array_keys($parents)[0];

			$universities = University::find_all_by_parent_id_and_verified($current->id, true);
			$careers = Career::find_all_by_university_id_and_verified($current->id, true);

			return
			[
				'current'			=> $current,
				'root_pretty_url'	=> $root_pretty_url,
				'root_name'			=> array_values($parents)[0],
				'parents'			=> array_diff_key($parents, [$root_pretty_url => 0]),
				'universities'		=> $universities,
				'careers'			=> $careers
			];
		}
	}