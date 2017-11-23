<?php
	class Place extends ActiveRecord\Model
	{
		/**
		 *	CREATE TABLE `places` (
		 *	  `id` int(11) NOT NULL,
		 *	  `parent_id` int(11) DEFAULT NULL,
		 *	  `name` varchar(255) NOT NULL,
		 *	  `pretty_url` varchar(255) NOT NULL
		 *	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
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
			['universities']
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
			$this->pretty_url = ApplicationController::getPrettyURL($this->name);

			$this->pretty_url .= '-' . count(self::all(['conditions' => ['pretty_url like ?', $this->pretty_url . '-%']]));
		}

		public static function getList()
		{
			$all = [];
			foreach(self::all() as $item)
			{
				$all[$item->id] = [$item->id => $item->name];

				$parent = $item->parent;

				while(!empty($parent))
				{
					$all[$item->id][$parent->id] = $parent->name;

					$parent = $parent->parent;
				}

				$all[$item->id] = array_reverse($all[$item->id], true);
			}

			uasort($all, function($a, $b)
			{ return strnatcmp(implode('/', $a), implode('/', $b)); });

			return $all;
		}
	}