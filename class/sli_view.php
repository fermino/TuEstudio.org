<?php
	class SliView extends ViewEngine
	{
		const EXTENSION = 'sli';

		private $restricted_tags =
		[
			'doctype'	=> ['<doctype $1>',		null],
			'|'			=> ['$1',				null]
		];

		private $string = '';

		public function parse() : bool
		{
			if(null !== ($lines = $this->getFile()))
			{
				$string = '';
				$tags_to_close = [];

				$line_count = count($lines);
				for($i = 0; $i < $line_count; $i++)
					$string .= $this->parseLine($lines[$i], $lines[$i + 1] ?? [0, null, null], $tags_to_close);

				$this->string = $string;

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
				return str_replace('$1', $line, $this->restricted_tags[$tag_name][$closing ? 1 : 0]);

			return '<'.($closing ? '/' : null).$tag_name.'>';
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

			// Log
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
			//extract($environment, EXTR_OVERWRITE);
			//return include $this->path;

			echo $this->string;

			return true;
		}
	}