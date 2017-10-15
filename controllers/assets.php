<?php
	class AssetsController extends ControllerBase
	{
		protected $unique_view = false;

		private $allowed_folders =
		[
			'css',
			'img',
			'js',
		];

		public function get(string $folder, string $file, string $ext)
		{
			if(in_array($folder, $this->allowed_folders))
			{
				$path = __DIR__ . '/../assets/' . $folder . '/' . $file . '.' . $ext;

				$realpath = realpath($path);
				// It is readable
				if(false !== $realpath)
				{
					if(in_array(basename(dirname($realpath)), $this->allowed_folders))
					{
						if(false !== readfile($path))
							return null;

						return 500;
					}
				}
			}

			return 404;
		}
	}