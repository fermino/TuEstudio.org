<?php
	class Career extends ActiveRecord\Model
	{
		/**
		 *	CREATE TABLE `careers` (
		 *	  `id` int(11) NOT NULL,
		 *	  `university_id` int(11) NOT NULL,
		 *	  `knowledge_area_id` int(11) NOT NULL,
		 *	  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
		 *	  `pretty_url` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
		 *	  `degree` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
		 *	  `length` tinyint(1) DEFAULT NULL,
		 *	  `middle_degree` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
		 *	  `middle_length` tinyint(1) DEFAULT NULL,
		 *	  `description` text COLLATE utf8_unicode_ci,
		 *	  `verified` tinyint(1) NOT NULL DEFAULT '0'
		 *	) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
		 *	
		 *	ALTER TABLE `careers`
		 *	  ADD PRIMARY KEY (`id`),
		 *	  ADD UNIQUE KEY `pretty_url` (`pretty_url`);
		 *	ALTER TABLE `careers` ADD FULLTEXT KEY `description` (`description`);
		 *	
		 *	ALTER TABLE `careers`
		 *	  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
		 */

		public static $belongs_to = [['university'], ['knowledge_area']];
		public static $has_many =
		[
			['career_places'],
			['places', 'through'	=> 'career_places']
		];

		public static $validates_length_of =
		[
			['name',			'within'	=> [1, 255]],
			['degree',			'maximum'	=> 255],
			['length', 			'maximum'	=> 1],
			['middle_degree',	'maximum'	=> 255],
			['middle_length', 	'maximum'	=> 1]
		];

		public static $attr_protected = ['pretty_url'];

		public static $validates_presence_of = [['university_id'], ['knowledge_area_id'], ['verified']];

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
	}