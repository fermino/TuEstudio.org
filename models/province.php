<?php
	class Province extends ActiveRecord\Model
	{
		/**
		 *	CREATE TABLE `provinces` (
		 *	  `id` int(11) NOT NULL,
		 *	  `name` varchar(255) NOT NULL,
		 *	  `pretty_url` varchar(255) NOT NULL
		 *	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		 *	
		 *	ALTER TABLE `provinces`
		 *	  ADD PRIMARY KEY (`id`),
		 *	  ADD UNIQUE KEY `pretty_url` (`pretty_url`);
		 *
		 *	ALTER TABLE `provinces`
		 *	  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
		 */

		public static $attr_protected = ['pretty_url'];

		public static $validates_length_of =
		[
			['name',		'within' => [1, 255]],
			['pretty_url',	'within' => [1, 255]]
		];

		public static $validates_uniqueness_of =
		[
			['pretty_url']
		];

		public function set_name($name)
		{
			$this->assign_attribute('name', $name);
			$this->pretty_url = ApplicationController::getPrettyURL($this->name);
		}
	}