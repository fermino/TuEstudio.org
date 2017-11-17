<?php
	class University extends ActiveRecord\Model
	{
		/**
		  *	CREATE TABLE `universities` (
		 *	  `id` int(11) NOT NULL,
		 *	  `parent_id` int(11) DEFAULT NULL,
		 *	  `place_id` int(11) NOT NULL,
		 *	  `name` varchar(255) NOT NULL,
		 *	  `pretty_url` varchar(255) NOT NULL,
		 *	  `web_address` varchar(255) NOT NULL,
		 *	  `email` varchar(255) NOT NULL,
		 *	  `phone` varchar(255) NOT NULL,
		 *	  `address` varchar(255) NOT NULL,
		 *	  `verified` tinyint(1) NOT NULL DEFAULT '0'
		 *	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		 *	
		 *	ALTER TABLE `universities`
		 *	  ADD PRIMARY KEY (`id`),
		 *	  ADD UNIQUE KEY `pretty_url` (`pretty_url`);
		 *	
		 *	ALTER TABLE `universities`
		 *	  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
		 */

		public static $belongs_to =
		[
			['parent', 'foreign_key' => 'parent_id', 'class_name' => 'University'],
			['place']
		];
		public static $has_many =
		[
			['universities', 'foreign_key' => 'parent_id', 'class_name' => 'University'],
			['careers']
		];

		public static $attr_protected = ['pretty_url'];

		public static $validates_length_of =
		[
			['name',		'within'	=> [1, 255]],
			['pretty_url',	'within'	=> [1, 255]],
			['phone',		'maximum'		=> 255],
			['email',		'maximum'		=> 255],
			['web_address',	'maximum'		=> 255],
			['address',		'maximum'		=> 255],
		];

		public static $validates_uniqueness_of = [['name'], ['pretty_url']];

		public static $validates_presence_of = [['place_id'], ['verified']];

		public function set_name($name)
		{
			$this->assign_attribute('name', $name);
			$this->pretty_url = ApplicationController::getPrettyURL($this->name);
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