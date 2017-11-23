<?php
	class Career extends ActiveRecord\Model
	{
		/**
		  *	CREATE TABLE `careers` (
		 *	  `id` int(11) NOT NULL,
		 *	  `university_id` int(11) NOT NULL,
		 *	  `knowledge_area_id` int(11) NOT NULL,
		 *	  `name` varchar(255) NOT NULL,
		 *	  `degree` varchar(255) DEFAULT NULL,
		 *	  `length` tinyint(1) DEFAULT NULL,
		 *	  `description` text NOT NULL,
		 *	  `verified` tinyint(1) NOT NULL DEFAULT '0'
		 *	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		 *	
		 *	ALTER TABLE `careers`
		 *	  ADD PRIMARY KEY (`id`);
		 *	
		 *	ALTER TABLE `careers`
		 *	  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
		 */

		public static $belongs_to = [['university'], ['knowledge_area']];

		public static $validates_length_of =
		[
			['name',		'within'	=> [1, 255]],
			['degree',		'maximum'	=> 255]
		];

		public static $validates_presence_of = [['university_id'], ['knowledge_area_id'], ['verified']];

		public static function getPlacesList()
		{
			/*$all = [];
			foreach(self::all() as $item)
				$all[$item->id] = CareerPlace::find_by_career_id($item->id);

			return $all;*/
		}
	}