<?php
	class CareerController extends ApplicationController
	{
		public function get($career_pretty_url)
		{
			$current = (Career::find_all_by_pretty_url_and_verified($career_pretty_url, true))[0] ?? null;

			if(empty($current))
				return '/';

			// $this->title = "{$current->name} en {$current->university->}"

			$university = $current->university;

			$parents = $university->getParentList(true, true);
			$root_pretty_url = array_keys($parents)[0];

			return
			[
				'current'				=> $current,
				'parsed_description'	=> Parsedown::instance()->setMarkupEscaped(true)->setBreaksEnabled(true)->setUrlsLinked(true)->text($current->description),
				'root_pretty_url'		=> $root_pretty_url,
				'root_name'				=> array_values($parents)[0],
				'parents'				=> array_diff_key($parents, [$root_pretty_url => 0]),
				//'knowledge_area'		=> $current->knowledge_area->getParentList()
			];
		}
	}