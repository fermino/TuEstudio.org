<?php
	class KnowledgeArea extends ActiveRecord\Model
	{
		/**
		 *	CREATE TABLE `knowledge_areas` (
		 *	  `id` int(11) NOT NULL,
		 *	  `parent_id` int(11) DEFAULT NULL,
		 *	  `name` varchar(255) NOT NULL,
		 *	  `pretty_url` varchar(255) NOT NULL,
		 *	  `description` varchar(255) DEFAULT NULL
		 *	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		 *	
		 *	ALTER TABLE `knowledge_areas`
		 *	  ADD PRIMARY KEY (`id`)
		 *	  ADD UNIQUE KEY `pretty_url` (`pretty_url`);
		 *	
		 *	ALTER TABLE `knowledge_areas`
		 *	  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
		 */

		public static $belongs_to = [['parent', 'foreign_key' => 'parent_id', 'class_name' => 'KnowledgeArea']];
		public static $has_many =
		[
			['areas', 'foreign_key' => 'parent_id', 'class_name' => 'KnowledgeArea'],
			['careers']
		];

		public static $attr_protected = ['pretty_url'];

		public static $validates_length_of =
		[
			['name',		'within'	=> [1, 255]],
			['pretty_url',	'within'	=> [1, 255]],
			['description', 'maximum'	=> 255]
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

		public static function getList()
		{
			$all = [];
			foreach(self::all() as $item)
				$all[$item->id] = $item->getParentList();

			uasort($all, function($a, $b)
			{ return strnatcmp(implode('/', $a), implode('/', $b)); });

			return $all;
		}

		public function getParentList(bool $reverse = true, $pretty_url_instead_id = false) : array
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