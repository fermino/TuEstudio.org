<?php
	class Province extends ActiveRecord\Model
	{
		/**
		 *	CREATE TABLE `provinces` (
		 *	  `id` int(11) NOT NULL,
		 *	  `name` varchar(255) NOT NULL
		 *	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		 *	
		 *	ALTER TABLE `provinces`
		 *	  ADD PRIMARY KEY (`id`),
		 *	  ADD UNIQUE KEY `name` (`name`);
		 *	
		 *	ALTER TABLE `provinces`
		 *	  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
		 */

		public static $validates_length_of =
		[
			['name',	'within' => [1, 255]]
		];

		public static $validates_uniqueness_of =
		[
			['name']
		];
	}