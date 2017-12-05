<?php
	class Place extends ActiveRecord\Model
	{
		/**
		 *	CREATE TABLE `places` (
		 *	  `id` int(11) NOT NULL,
		 *	  `parent_id` int(11) DEFAULT NULL,
		 *	  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
		 *	  `pretty_url` varchar(255) COLLATE utf8_unicode_ci NOT NULL
		 *	) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
		 *	
		 *	ALTER TABLE `places`
		 *	  ADD PRIMARY KEY (`id`),
		 *	  ADD UNIQUE KEY `pretty_url` (`pretty_url`);
		 *	
		 *	ALTER TABLE `places`
		 *	  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
		 */

		public static $belongs_to = [['parent', 'foreign_key' => 'parent_id', 'class_name' => 'Place']];
		public static $has_many =
		[
			['places', 'foreign_key' => 'parent_id', 'class_name' => 'Place'],
			['universities'],
			['career_places'],
			['careers', 'through'	=> 'career_places']
		];

		public static $attr_protected = ['pretty_url'];

		public static $validates_length_of =
		[
			['name',		'within'	=> [1, 255]],
			['pretty_url',	'within'	=> [1, 255]]
		];

		public static $validates_uniqueness_of = [['pretty_url']];

		public function set_name($name)
		{
			$this->assign_attribute('name', $name);

			if(empty($this->pretty_url))
			{
				$this->pretty_url = ApplicationController::getPrettyURL($this->name);
				$this->pretty_url .= '-' . count(self::all(['conditions' => ['pretty_url like ?', $this->pretty_url . '-%']]));
			}
		}

		public static function getList(bool $reverse = true, bool $pretty_url_instead_id = false) : array
		{
			$all = [];
			foreach(self::all() as $item)
				$all[$item->id] = $item->getParentList($reverse, $pretty_url_instead_id);

			uasort($all, function($a, $b)
			{ return strnatcmp(implode('/', $a), implode('/', $b)); });

			return $all;
		}

		public function getParentList(bool $reverse = true, bool $pretty_url_instead_id = false) : array
		{
			$parent = $this;

			while(!empty($parent))
			{
				$data[($pretty_url_instead_id ? $parent->pretty_url : $parent->id)] = $parent->name;

				$parent = $parent->parent;
			}

			return $reverse ? array_reverse($data, true) : $data;
		}
	}