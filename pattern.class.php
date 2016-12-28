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
	}