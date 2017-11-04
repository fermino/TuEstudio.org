<?php
	class City extends ActiveRecord\Model
	{
		/**
		 *	CREATE TABLE `cities` (
		 *	  `id` int(11) NOT NULL,
		 *	  `province_id` int(11) NOT NULL,
		 *	  `name` varchar(255) NOT NULL
		 *	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		 *	
		 *	ALTER TABLE `cities`
		 *	  ADD PRIMARY KEY (`id`);
		 *	
		 *	ALTER TABLE `cities`
		 *	  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
		 */

		public static $belongs_to = ['province'];

		public static $validates_length_of =
		[
			['name',	'within' => [1, 255]]
		];
	}