<?php
	class UniversityController extends ApplicationController
	{
		public function get($university_id)
		{
			$current = (University::find_all_by_id_and_verified($university_id, true))[0] ?? null;

			if(empty($current))
				return '/';

			$universities = University::find_all_by_parent_id_and_verified($university_id, true);
			$careers = Career::find_all_by_university_id_and_verified($university_id, true);

			return
			[
				'current'		=> $current,
				'universities'	=> $universities,
				'careers'		=> $careers
			];
		}
	}