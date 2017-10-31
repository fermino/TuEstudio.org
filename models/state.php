<?php
	class State extends ActiveRecord\Model
	{
		/**
		 *	CREATE TABLE `states` (
		 *	  `id` int(11) NOT NULL,
		 *	  `name` varchar(32) NOT NULL
		 *	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		 *	
		 *	ALTER TABLE `states`
		 *	  ADD PRIMARY KEY (`id`),
		 *	  ADD UNIQUE KEY `name` (`name`);
		 *	
		 *	ALTER TABLE `states`
		 *	  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
		 */

		public static $validates_length_of =
		[
			['name',	'within' => [1, 32]]
		];

		public static $validates_uniqueness_of =
		[
			['name']
		];
	}