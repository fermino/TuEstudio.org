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
		 *	  ADD PRIMARY KEY (`id`);
		 *	
		 *	ALTER TABLE `knowledge_areas`
		 *	  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
		 */

		public static $belongs_to = [['parent', 'foreign_key' => 'parent_id', 'class_name' => 'KnowledgeArea']];
		public static $has_many = [['areas', 'foreign_key' => 'parent_id', 'class_name' => 'KnowledgeArea']];

		public static $attr_protected = ['pretty_url'];

		public static $validates_length_of =
		[
			['name',		'within'	=> [1, 255]],
			['pretty_url',	'within'	=> [1, 255]],
			['description', 'maximum'	=> 255]
		];

		public static $validates_uniqueness_of = [['name'], ['pretty_url']];

		public function set_name($name)
		{
			$this->assign_attribute('name', $name);
			$this->pretty_url = ApplicationController::getPrettyURL($this->name);
		}
	}