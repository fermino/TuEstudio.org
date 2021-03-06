<?php
	class SliView extends ViewEngine
	{
		public const EXTENSION = 'sli';

		private $restricted_tags =
		[
			// HTML5 standard
			'doctype'	=> ['<!doctype $->'],
			'meta'		=> ['<meta $->'],
			'title'		=> ['<title>$-', '</title>'],
			'br'		=> ['<br>'],
			'input'		=> ['<input $->'],

			// Code shortcuts
			'rjs'		=> ['<script src="$-" defer>', '</script>'],
			'rcss'		=> ['<link rel="stylesheet" href="$-">'],
			'rimg'		=> ['<img src="$-">'],

			'js'		=> ['<script src="//=raw{_SERVER[SERVER_NAME]}/assets/js/$-.js" defer>', '</script>'],
			'css'		=> ['<link rel="stylesheet" href="//=raw{_SERVER[SERVER_NAME]}/assets/css/$-.css">'],
			'img'		=> ['<img src="//=raw{_SERVER[SERVER_NAME]}/assets/img/$-">'],

			// Display'ers
			'|'			=> ['$-'],
			'='			=> ['echo htmlspecialchars($$-, ENT_QUOTES, \'UTF-8\');', null, true],
			'=raw'		=> ['echo $$-;', null, true],

			// Helpers
			'render'	=> ['if(null !== ($_v = ViewEngine::loadView(\'$-\', $this->logger)) && $_v->parse()) $_v->display($environment);', null, true],
			'rendervar'	=> ['if(null !== ($_v = ViewEngine::loadView($$-, $this->logger)) && $_v->parse()) $_v->display($environment);', null, true],

			// Comments
			'//'		=> [null],

			// PHP
			'--'		=> ['$- {', '}', true],
			'!-'		=> ['$-', null, true],
			'=set'		=> ['$environment[\'$0\'] = ${\'$0\'} = \'$1\';', null, true]
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

						// Needs to be here as closures can't be stored as properties
						$post_parse =
						[
							[
								'/=(raw|json|rawjson|pjson)?{([a-zA-Z0-9_]+)(\\[[\'"]?([a-zA-Z0-9_-]+)[\'"]?]|->([a-zA-Z0-9_-]+))?}/',
								function($matches)
								{
									$c = count($matches);

									if(6 === $c)
										$v = '${\'$2\'}->{\'$5\'}';
									else if(5 === $c)
										$v = '${\'$2\'}[\'$4\']';
									else
										$v = '${\'$2\'}';

									switch($matches[1])
									{
										case 'raw':
											return "echo {$v};";
										case 'json':
											return "echo htmlspecialchars(json_encode({$v}), ENT_QUOTES, 'UTF-8');";
										case 'rawjson':
											return "echo json_encode({$v});";
										case 'pjson':
											return "echo htmlspecialchars(json_encode({$v}, JSON_PRETTY_PRINT), ENT_QUOTES, 'UTF-8');";
										default:
											return "echo htmlspecialchars({$v}, ENT_QUOTES, 'UTF-8');";
									}
								}
							]
						];

						foreach($post_parse as $regex)
							$string = preg_replace_callback($regex[0], function($matches) use($regex)
							{
								$c = count($matches);
								$s = str_replace('$-', $c - 1, $regex[1]($matches));

								for($i = 0; $i < $c; $i++)
									$s = str_replace('$'.$i, $matches[$i], $s);

								return '<?php ' . $s . ' ?>';
							}, $string);

						// The file was successfully compiled and saved
						if(strlen($string) === file_put_contents($this->compiled_path, $string))
						{
							// Delete previous compiled views
							foreach(glob(__DIR__.'/../views/cache/' . $this->view_name . '*_.php') as $file)
								if($file !== $this->compiled_path)
									unlink($file);

							return true;
						}

						// If can't save

						$this->compiled_path = null;

						$this->logger->critical('[ViewEngine::parse] The compiled view could not be saved',
						[
							'engine'		=> get_class($this),
							'view'			=> $this->view_name,
							'path'			=> $this->path,
							'compiled_path'	=> $this->compiled_path
						]);
						return false;
					}

					$this->logger->critical('[ViewEngine::parse] The not-compiled view is not readable',
					[
						'engine'		=> get_class($this),
						'view'			=> $this->view_name,
						'path'			=> $this->path,
						'compiled_path'	=> $this->compiled_path
					]);
					return false;
				}

				// The file is already compiled
				return true;
			}

			$this->logger->critical('[ViewEngine::parse] The not-compiled view is not readable',
			[
				'engine'		=> get_class($this),
				'view'			=> $this->view_name,
				'path'			=> $this->path
			]);
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
				$string = str_replace('$-', $line, $this->restricted_tags[$tag_name][$closing ? 1 : 0] ?? null);

				$words = explode(' ', $line);
				for($i = count($words) - 1; $i >= 0; $i--)
					$string = str_replace('$'.$i, $words[$i], $string);

				if(!empty($this->restricted_tags[$tag_name][2]) && !empty($this->restricted_tags[$tag_name][$closing ? 1 : 0]))
					$string = '<?php ' . $string . ' ?>';

				if(!empty($string))
					return $string . "\n";

				return '';
			}

			return '<'.($closing ? '/' : null).$tag_name.(!empty($line) ? ' '.$line : null).">\n";
		}

		private function getFile() : ?array
		{
			if($this->isReadable())
			{
				$lines = file($this->path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

				if(false !== $lines && is_array($lines))
				{
					// Delete empty lines
					$lines = array_values(array_filter($lines, function($v) { return rtrim($v) !== ''; }));

					// getDepth() and tag - rest
					$lines = array_map(function($v)
					{
						$depth = $this::getDepth($v);

						$v		= ltrim($v);
						$strpos	= $this::strpos_array($v, [' ', "\t"]);

						return
						[
							$depth,
							false !== $strpos ? substr($v, 0, $strpos)	: $v,
							false !== $strpos ? substr($v, $strpos + 1)	: null
						];
					}, $lines);

					return $lines;
				}

				$this->logger->critical('[ViewEngine::getFile] The not-compiled view is not readable',
				[
					'engine'		=> get_class($this),
					'view'			=> $this->view_name,
					'path'			=> $this->path,
					'compiled_path'	=> $this->compiled_path
				]);
				return null;
			}

			$this->logger->critical('[ViewEngine::getFile] The not-compiled view is not readable',
			[
				'engine'		=> get_class($this),
				'view'			=> $this->view_name,
				'path'			=> $this->path,
				'compiled_path'	=> $this->compiled_path
			]);
			return null;
		}

		private static function strpos_array(string $haystack, array $needles, int $offset = 0)
		{
			foreach($needles as $needle)
			{
				if(false !== ($pos = strpos($haystack, $needle, $offset)))
					return $pos;
			}

			return false;
		}

		private static function getDepth(string $string) : int
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

		public function display(array $environment = []) : bool
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
				'environment'	=> $environment
			]);
			return false;
		}
	}