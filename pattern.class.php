<?php

	require_once("black_chain.class.php");
	require_once("pattern_clue.class.php");

	class pattern
	{
		/** @var int width */
		public $width;
		/** @var int height */
		public $height;
		/** @var pattern_clue[] rows of clues  */
		public $rows;
		/** @var pattern_clue[] columns of clues  */
		public $columns;

		function __construct(int $width, int $height)
		{
			if($width < 1)
			{
				throw new Exception('Width should be a positive integer');
			}
			if($height < 1)
			{
				throw new Exception('Height should be a positive integer');
			}

			$this->width = $width;
			$this->height = $height;

			$this->rows = array();
			foreach(range(1, $this->height) as $row_nr)
			{
				$this->rows[$row_nr] = new pattern_clue($this->width);
			}

			$this->columns = array();
			foreach(range(1, $this->width) as $col_nr)
			{
				$this->columns[$col_nr] = new pattern_clue($this->height);
			}
		}

		/**
		 * @param string $type row or column
		 * @param integer $position row-nr or column-nr
		 * @param integer[] $sizes length of black blocks
		 *
		 * @throws Exception from black_chain->__construct()
		 *
		 * @return bool
		 */
		function set_clue(string $type, int $position, $sizes) : bool
		{
			if(strtolower(substr($type, 0, 1)) == 'r')
			{
				return $this->rows[$position]->set_clue($sizes);
			}
			else
			{
				return $this->columns[$position]->set_clue($sizes);
			}
		}

		/**
		 * @param integer $position row-nr
		 * @param integer[] $sizes length of black blocks
		 *
		 * @throws Exception from black_chain->__construct()
		 *
		 * @return bool
		 */
		function set_row_clue($position, $sizes)
		{
			return $this->rows[$position]->set_clue($sizes);
		}

		/**
		 * @param integer $position column-nr
		 * @param integer[] $sizes length of black blocks
		 *
		 * @throws Exception from black_chain->__construct()
		 *
		 * @return bool
		 */
		function set_column_clue($position, $sizes)
		{
			return $this->columns[$position]->set_clue($sizes);
		}

		/**
		 * Set a given cordinat as black
		 *
		 * @param int $col_nr cordinat for column
		 * @param int $row_nr cordinat for row
		 *
		 * @return int number of changes
		 */
		function mark_black(int $col_nr, int $row_nr) : int
		{
			$changes = $this->rows[$row_nr]->update_key(array($col_nr => 0));
			$changes += $this->columns[$col_nr]->update_key(array($row_nr => 0));
			return $changes;
		}

		/**
		 * Set a given cordinat as white
		 *
		 * @param int $col_nr cordinat for column
		 * @param int $row_nr cordinat for row
		 *
		 * @return int number of changes
		 */
		function mark_white(int $col_nr, int $row_nr) : int
		{
			$changes = $this->rows[$row_nr]->update_key(array($col_nr => 0));
			$changes += $this->columns[$col_nr]->update_key(array($row_nr => 0));
			return $changes;
		}

		/**
		 * @return integer[][] row_nr->col_nr->value
		 */
		function get_rows()
		{
			$row_keys = array();

			foreach($this->rows as $row_nr => $row)
			{
				$row_keys[$row_nr] = $row->keys;
			}

			return $row_keys;
		}

		/**
		 * @return integer[][] col_nr->row_nr->value
		 */
		function get_cols()
		{
			$col_keys = array();

			foreach($this->columns as $col_nr => $col)
			{
				$col_keys[$col_nr] = $col->keys;
			}

			return $col_keys;
		}

		/**
		 * Compare known white and black from columns and rows
		 *
		 * @return int number of changes
		 */
		function sync()
		{
			$changes = 0;
			$rows = $this->get_rows();
			$updated_rows = array();
			$cols = $this->get_cols();
			$updated_cols = array();

			foreach($rows as $row_nr => $row_keys)
			{
				foreach($row_keys as $col_nr => $value)
				{
					if(!isset($cols[$col_nr][$row_nr]))
					{
						$updated_cols[$col_nr][$row_nr] = $value;
					}
				}
			}

			foreach($cols as $col_nr => $col_keys)
			{
				foreach($col_keys as $row_nr => $value)
				{
					if(!isset($rows[$row_nr][$col_nr]))
					{
						$updated_rows[$row_nr][$col_nr] = $value;
					}
				}
			}

			if($updated_rows)
			{
				foreach($updated_rows as $row_nr => $row_keys)
				{
					$changes += $this->rows[$row_nr]->update_key($row_keys);
				}
			}

			if($updated_cols)
			{
				foreach($updated_cols as $col_nr => $col_keys)
				{
					$changes += $this->columns[$col_nr]->update_key($col_keys);
				}
			}

			return $changes;
		}

		/**
		 * load a pattern from file
		 *
		 * @param string $filename
		 *
		 * @return pattern
		 */
		static function load(string $filename) : pattern
		{
			$raw_file = file_get_contents($filename);
			$saved = json_decode($raw_file, TRUE);
			if(!$saved)
			{
				if(substr($raw_file, 0, 12) == 'SAVEFILE:41:')
				{
					$saved = pattern::parse_sgt($raw_file);
				}
				else
				{
					$saved = array();
					$rows = explode("\n", $raw_file);
					if(count($rows) > 5)
					{
						$saved['width'] = (int) $rows[0];
						$saved['height'] = (int) $rows[1];
						$separator = trim($rows[2]);
						if(empty($separator) AND $saved['width'] > 0 AND $saved['height'] > 0 AND count($rows) > $saved['height'] + $saved['width'] + 3)
						{
							$saved['col'] = array_slice($rows, 3, $saved['width']);
							$saved['row'] = array_slice($rows, $saved['width'] + 4, $saved['height']);
						}
						else if($separator)
						{
							foreach($rows as $row_nr => $row)
							{
								if(empty(trim($row)))
								{
									$saved['col'] = array_filter(array_slice($rows, 0, $row_nr));
									$saved['row'] = array_filter(array_slice($rows, $row_nr + 1));
									$saved['width'] = count($saved['col']);
									$saved['height'] = count($saved['row']);
								}
							}
						}

						if(empty($saved['col']))
						{
							$saved = NULL;
						}
					}

				}
			}
			if($saved)
			{
				unset($raw_file);

				// Size
				$pattern = new pattern($saved['width'], $saved['height']);

				// Rows
				$offset = empty($saved['row'][0]) ? 0 : 1;
				foreach($saved['row'] as $s_row_nr => $row_clue)
				{
					$row_nr = $s_row_nr + $offset;
					if($row_nr AND $row_clue)
					{
						$pattern->set_row_clue($row_nr, explode(' ', $row_clue));
					}
				}

				// Columns
				$offset = empty($saved['col'][0]) ? 0 : 1;
				foreach($saved['col'] as $s_col_nr => $col_clue)
				{
					$col_nr = $s_col_nr + $offset;
					if($col_nr AND $col_clue)
					{
						$pattern->set_column_clue($col_nr, explode(' ', $col_clue));
					}
				}

				if($saved['plan'])
				{
					foreach($saved['plan'] as $row_nr => $row_keys)
					{
						$pattern->rows[$row_nr]->update_key($row_keys);
					}
				}

				return $pattern;
			}
			return NULL;
		}

		function save(string $filename)
		{
			$saved = array();
			$saved['width'] = $this->width;
			$saved['height'] = $this->height;
			$saved['row'] = array('');
			foreach($this->rows as $row_nr => $row)
			{
				$saved['row'][$row_nr] = implode(' ', $row->clue);
			}
			$saved['col'] = array('');
			foreach($this->columns as $col_nr => $col)
			{
				$saved['col'][$col_nr] = implode(' ', $col->clue);
			}
			$saved['plan'] = $this->get_rows();
			if($filename)
			{
				file_put_contents($filename, json_encode($saved, JSON_NUMERIC_CHECK + JSON_PRETTY_PRINT));
			}
			return $saved;
		}

		function export_png(string $filename)
		{
			$img = imagecreate($this->width, $this->height);
			$gray = imagecolorallocate($img, 127, 127, 127);
			$white = imagecolorallocate($img, 255, 255, 255);
			$black = imagecolorallocate($img, 0, 0, 0);

			foreach($this->get_rows() as $row_nr => $row_data)
			{
				foreach($row_data as $col_nr => $value)
				{
					$color = (($value > 0) ? $black : $white);
					imagesetpixel($img, $col_nr - 1, $row_nr - 1, $color);
				}
			}

			imagepng($img, $filename);
		}

		static function parse_sgt($file)
		{
			$saved = array();
			$parameters = array();
			foreach(explode("\n", $file) AS $row)
			{
				$parts = explode(':', $row, 3);
				$parameters[trim($parts[0])] = trim($parts[2]);
			}
			$size = explode('x', $parameters['PARAMS']);
			$saved['width'] = $size[0];
			$saved['height'] = $size[1];
			$saved['col'] = array('');
			$saved['row'] = array('');
			$patterns = explode('/', $parameters['DESC']);
			foreach($patterns as $p_nr => $p_code)
			{
				if($p_nr < $saved['width'])
				{
					$saved['col'][] = str_replace('.', ' ', $p_code);
				}
				else
				{
					$saved['row'][] = str_replace('.', ' ', $p_code);
				}
			}

			return $saved;
		}
	}