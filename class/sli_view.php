<?php
	class SliView extends ViewEngine
	{
		const EXTENSION = 'sli';

		private $restricted_tags =
		[
			'doctype'	=> [ '<doctype $1>'											, null			],
			'title'		=> [ '<title>$1'											, '</title>'	],
			'|'			=> [ '$1'													, null			],
			'='			=> [ 'echo htmlspecialchars($$1, ENT_QUOTES, \'UTF-8\');'	, null,		true],
			'=raw'		=> [ 'echo $$1;'											, null,		true],
			'render'	=> [ 'if(null !== ($_v = ViewEngine::loadView(\'$1\'[0] === \'$\' ? $1 : \'$1\', $this->logger)) && $_v->parse()) $_v->display($environment);', null, true]
		];

		private $post_parse =
		[
			[
				'/=([A-Za-z0-9]+)/',
				'echo htmlspecialchars($$1, ENT_QUOTES, \'UTF-8\');'
			]
		];

		private $compiled_path = null;

		public function parse() : bool
		{
			if($this->isReadable())
			{
				$sli_mtime = filemtime($this->path);

				$this->compiled_path = __DIR__.'/../views/cache/'. $this->view_name . $sli_mtime . '_.php';

				if(!is_readable($this->compiled_path))
				{
					// Let's compile it!
					if(null !== ($lines = $this->getFile()))
					{
						$string = '';
						$tags_to_close = [];

						$line_count = count($lines);
						for($i = 0; $i < $line_count; $i++)
							$string .= $this->parseLine($lines[$i], $lines[$i + 1] ?? [0, null, null], $tags_to_close);

						foreach($this->post_parse as $regex)
							$string = preg_replace_callback($regex[0], function($matches) use($regex) { return '<?php ' . str_replace('$1', $matches[1], $regex[1]) . ' ?>'; }, $string);

						// If can't save
						if(strlen($string) !== file_put_contents($this->compiled_path, $string))
						{
							$this->compiled_path = null;

							// Log error
						}
						// The file was successfully compiled and saved
						else
						{
							// Delete previous compiled views
							foreach(glob(__DIR__.'/../views/cache/' . $this->view_name . '*_.php') as $file)
								if($file !== $this->compiled_path)
									unlink($file);

							return true;
						}
					}
					else
					{
						// Log error
					}
				}
				// The file is already compiled
				else
					return true;
			}

			return false;
		}

		// This will add $current's tag and let everything ready to add $next's one
		private function parseLine(array $current, array $next, array &$tags_to_close) : string
		{
			$string = $this->addTag(false, $current[1], $current[2]);
			$tags_to_close[] = $current[1];

			// If the next line indent level is smaller
			// If the next line is a sibling
			if($next[0] <= $current[0])
			{
				// We must close the tags
				for($i = count($tags_to_close) - $next[0]; $i > 0; $i--)
					$string .= $this->addTag(true, array_pop($tags_to_close));
			}

			return $string;
		}

		private function addTag(bool $closing, string $tag_name, string $line = null) : string
		{
			if(isset($this->restricted_tags[$tag_name]))
			{
				$string = str_replace('$1', $line, $this->restricted_tags[$tag_name][$closing ? 1 : 0]);

				if(!empty($this->restricted_tags[$tag_name][2]) && !empty($this->restricted_tags[$tag_name][$closing ? 1 : 0]))
					$string = '<?php ' . $string . '?>';

				return $string;
			}

			return '<'.($closing ? '/' : null).$tag_name.(!empty($line) ? ' '.$line : null).'>';
		}

		private function getFile() : ?array
		{
			if($this->isReadable())
			{
				$lines = file($this->path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

				if(false !== $lines && is_array($lines))
				{
					// Trim lines and delete empty ones
					$lines = array_values(array_filter(array_map(function($v) { return rtrim($v); }, $lines), function($v) { return $v !== ''; }));

					// getDepth() and tag - rest
					$lines = array_map(function($v)
					{
						$depth = $this->getDepth($v);

						$v		= ltrim($v);
						$strpos	= strpos($v, ' ');

						return
						[
							$depth,
							false !== $strpos ? substr($v, 0, $strpos)	: $v,
							false !== $strpos ? substr($v, $strpos + 1)	: null
						];
					}, $lines);

					return $lines;
				}

				// Log
				return null;

			}

			$this->logger->critical('[ViewEngine::parseLine] The not-compiled view is not readable',
			[
				'engine'		=> get_class($this),
				'view'			=> $this->view_name,
				'path'			=> $this->path,
				'environment'	=> $environment
			]);
			return null;
		}

		private function getDepth(string $string) : int
		{
			$depth = 0;

			$length = strlen($string);

			for($i = 0; $i < $length; $i++)
			{
				if("\t" === $string[$i])
					$depth++;
				else
					break;
			}

			return $depth;
		}

		public function display(array $environment) : bool
		{
			if(!empty($this->compiled_path))
			{
				extract($environment, EXTR_OVERWRITE);

				return include $this->compiled_path;
			}

			$this->logger->critical('[ViewEngine::display] The compiled view is not readable',
			[
				'engine'		=> get_class($this),
				'view'			=> $this->view_name,
				'path'			=> $this->path,
				'compiled_path'	=> $this->compiled_path,
				'environment'	=> $environment
			]);
			return false;
		}
	}