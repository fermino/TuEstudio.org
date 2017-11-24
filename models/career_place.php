<?php
	class CareerPlace extends ActiveRecord\Model
	{
		/**
		 *	CREATE TABLE `career_places` (
		 *	  `id` int(11) NOT NULL,
		 *	  `career_id` int(11) NOT NULL,
		 *	  `place_id` int(11) NOT NULL,
		 *	  `address` varchar(255) NOT NULL
		 *	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		 *	
		 *	ALTER TABLE `career_places`
		 *	  ADD PRIMARY KEY (`id`);
		 *	
		 *	ALTER TABLE `career_places`
		 *	  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
		 */

		public static $belongs_to = [['career'], ['place']];

		public static $validates_presence_of = [['career_id'], ['place_id']];
	}