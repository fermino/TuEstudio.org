<?php
	class User extends ActiveRecord\Model
	{
		/**
		 * CREATE TABLE `users` (
		 *   `id` int(11) NOT NULL,
		 *   `email` varchar(255) NOT NULL,
		 *   `first_name` varchar(255) NOT NULL,
		 *   `last_name` varchar(255) NOT NULL,
		 *   `gender` varchar(1) NOT NULL,
		 *   `google_profile_id` varchar(255) NULL,
		 *   `google_profile_picture` varchar(255) NULL,
		 *   `is_admin` tinyint(1) NOT NULL DEFAULT '0',
		 *   `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
		 *   `updated_at` timestamp NULL DEFAULT NULL
		 * ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		 * 
		 * ALTER TABLE `users`
		 *   ADD PRIMARY KEY (`id`),
		 *   ADD UNIQUE KEY `email` (`email`);
		 */

		public static $attr_protected =
		[
			'is_admin',
			'created_at',
			'updated_at'
		];

		public static $validates_prescense_of =
		[
			['email'],
			['first_name'],
			['last_name'],
			['gender']
		];

		public static $validates_length_of =
		[
			['email',		'maximum' => 255],
			['first_name',	'maximum' => 255],
			['last_name',	'maximum' => 255],
			['gender',		'maximum' => 1]
		];

		public static $validates_uniqueness_of =
		[
			['email', 'message' => 'has been already taken']
		];

		public function validate()
		{
			if(false === filter_var($this->email, FILTER_VALIDATE_EMAIL))
				$this->errors->add('email', ActiveRecord\Errors::$DEFAULT_ERROR_MESSAGES['invalid']);
		}
	}