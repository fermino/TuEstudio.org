<?php
	class AssetsController extends ApplicationController
	{
		protected $unique_view = false;

		private $allowed_folders =
		[
			'css'	=> 'text/css',
			'img'	=> null,
			'js'	=> 'application/javascript'
		];

		public function get(string $folder, string $file, string $ext)
		{
			if(array_key_exists($folder, $this->allowed_folders))
			{
				$path = __DIR__ . '/../assets/' . $folder . '/' . $file . '.' . $ext;

				$realpath = realpath($path);
				// It is readable
				if(false !== $realpath)
				{
					if(in_array(basename(dirname($realpath)), array_keys($this->allowed_folders)))
					{
						if(empty($this->allowed_folders[$folder]))
						{
							$f = finfo_open(FILEINFO_MIME_TYPE);
							$mime = finfo_file($f, $realpath);
							finfo_close($f);
						}
						else
							$mime = $this->allowed_folders[$folder];
						
						header('Content-Type: ' . $mime);

						if(false !== readfile($realpath))
							return null;

						return 500;
					}
				}
			}

			return 404;
		}
	}