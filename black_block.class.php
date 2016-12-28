<?php

	/**
	 * class for a black block in a black chain
	 */
	class black_block
	{
		/** @var integer the length of the block */
		public $size;

		/** @var integer[] list of posible start positions */
		public $starts;

		/**
		 * Create a black block
		 *
		 * @param integer $size the length of the block
		 * @param integer $min_start first posible start location
		 * @param integer $max_start last posible start location
		 *
		 * @throws Exception
		 */
		function __construct(int $size, int $min_start, int $max_start)
		{
			if($size < 1)
			{
				throw new Exception('Size should be a positive integer');
			}
			if($min_start < 1)
			{
				throw new Exception('Min_start should be a positive integer');
			}
			if($max_start < $min_start)
			{
				throw new Exception('max_start should be at least min_start');
			}

			// Store size/length in object
			$this->size = $size;

			// create a list from min_start to max_start
			$starts = range($min_start, $max_start);

			// use values as keys, and store as posible start locations
			$this->starts = array_combine($starts, $starts);
		}

		/**
		 * @return int|bool Lowest posible start location
		 */
		function min_start()
		{
			if($this->starts)
			{
				return min($this->starts);
			}
			else
			{
				return FALSE;
			}
		}

		/**
		 * @return int|bool Highest posible start location
		 */
		function max_start()
		{
			if($this->starts)
			{
				return max($this->starts);
			}
			else
			{
				return FALSE;
			}
		}

		/**
		 * Return the lowest posible end location (start + length - 1)
		 *
		 * @return int|bool Lowest posible end location
		 */
		function min_end()
		{
			if($this->starts)
			{
				return $this->size - 1 + $this->min_start();
			}
			else
			{
				return FALSE;
			}
		}

		/**
		 * Return the highest posible end location (start + length - 1)
		 *
		 * @return int|bool Highest posible end location
		 */
		function max_end()
		{
			if($this->starts)
			{
				return $this->size - 1 + $this->max_start();
			}
			else
			{
				return FALSE;
			}
		}

		/**
		 * filter startpositions by black mark
		 *
		 * @param integer $pos the position that have the black mark
		 *
		 * @return boolean TRUE if changes was made
		 */
		function mark_black(int $pos) : bool
		{
			// No changes so far
			$change = FALSE;

			// white is needed left of start position
			// so can't be a start right of a black mark
			if(isset($this->starts[$pos + 1]))
			{
				unset($this->starts[$pos + 1]);
				$change = TRUE;
			}

			// white is needed right of the end position (end = start + length - 1)
			// so can't be a end left of the black mark (pos = end + 1)
			// start = end - length + 1, end = pos -1, start = pos - length
			if(isset($this->starts[$pos - $this->size]))
			{
				unset($this->starts[$pos - $this->size]);
				$change = TRUE;
			}

			// return TRUE if any changes was made
			return $change;
		}

		/**
		 * filter startpositions by white mark
		 *
		 * @param integer $pos the position that have the white mark
		 *
		 * @return boolean TRUE if changes was made
		 */
		function mark_white(int $pos) : bool
		{
			// No changes so far
			$change = FALSE;

			// from start pos to end pos need to be black
			// so can't have a white between start and end
			// pos in [start, end] = [start, start + length - 1] =>
			// start in [pos - length + 1, pos]
			$blocked_from = $pos + 1 - $this->size;
			$blocked_to = $pos;

			// test all start positions
			foreach($this->starts as $spos)
			{
				// before range, skip
				if($spos < $blocked_from)
				{
					continue;
				}
				// after range, done
				if($blocked_to < $spos)
				{
					break;
				}
				// in range, remove
				unset($this->starts[$spos]);
				$change = TRUE;
			}

			// return TRUE if changed
			return $change;
		}

		/**
		 * Set a new lowest starting position
		 *
		 * @param integer $new_pos new lowest starting location
		 *
		 * @return boolean TRUE if changed
		 */
		function new_min_start(int $new_pos) : bool
		{
			// No changes so far
			$change = FALSE;

			// test all start positions
			foreach($this->starts as $spos)
			{
				// if starts before new lowest start
				if($spos < $new_pos)
				{
					// remove
					unset($this->starts[$spos]);
					$change = TRUE;
				}
				// if after new lowest start, done
				else
				{
					break;
				}
			}

			// return TRUE if changed
			return $change;
		}

		/**
		 * Set a new highest starting position
		 *
		 * @param integer $new_pos new highest starting location
		 *
		 * @return boolean TRUE if changed
		 */
		function new_max_start(int $new_pos) : bool
		{
			// No changes so far
			$change = FALSE;

			// test all start positions
			foreach($this->starts as $spos)
			{
				// if starts before new highest start, skip
				if($spos <= $new_pos)
				{
					continue;
				}

				// remove
				unset($this->starts[$spos]);
				$change = TRUE;
			}

			// return TRUE if changed
			return $change;
		}

		/**
		 * Set a new lowest end position
		 *
		 * @param integer $new_pos new lowest emd location
		 *
		 * @return boolean TRUE if changed
		 */
		function new_min_end(int $new_pos) : bool
		{
			// Calculate new lowest start position (end = start + length - 1)
			return $this->new_min_start($new_pos + 1 - $this->size);
		}

		/**
		 * Set a new highest end position
		 *
		 * @param integer $new_pos new highest emd location
		 *
		 * @return boolean TRUE if changed
		 */
		function new_max_end(int $new_pos) : bool
		{
			// Calculate new highest start position (end = start + length - 1)
			return $this->new_max_start($new_pos + 1 - $this->size);
		}

		/**
		 * Check if this block can be at a given position
		 *
		 * @param integer $pos the position to check
		 *
		 * @return boolean TRUE if any startposition makes the given position black
		 */
		function can_be(int $pos) : bool
		{
			// pos in [start, end] = [start, start + length - 1] =>
			// start in [pos - length + 1, pos]
			$block_from = $pos + 1 - $this->size;
			$block_to = $pos;

			// test all start positions
			foreach($this->starts as $spos)
			{
				// before? skip
				if($spos < $block_from)
				{
					continue;
				}

				// after?, done
				if($block_to < $spos)
				{
					break;
				}

				// match, return TRUE
				return TRUE;
			}
			return FALSE;
		}

		/**
		 * Check where this block can start given a black position
		 *
		 * @param integer $pos the black position to check
		 *
		 * @return integer[] list of start positions
		 */
		function can_be_starts(int $pos) : array
		{
			/** @var integer[] $positions list of start positions */
			$positions = array();

			// pos in [start, end] = [start, start + length - 1] =>
			// start in [pos - length + 1, pos]
			$block_from = $pos + 1 - $this->size;
			$block_to = $pos;

			// test all start positions
			foreach($this->starts as $spos)
			{
				// before? skip
				if($spos < $block_from)
				{
					continue;
				}

				// after?, done
				if($block_to < $spos)
				{
					break;
				}

				// match, store
				$positions[$spos] = $spos;
			}

			// return positions
			return $positions;
		}

		/**
		 * Check if this block most be at a given position
		 *
		 * @param integer $pos the position to check
		 *
		 * @return boolean TRUE if all startposition makes the given position black
		 */
		function most_be(int $pos) : bool
		{
			// pos in [start, end] = [start, start + length - 1] =>
			// start in [pos - length + 1, pos]
			$block_from = $pos + 1 - $this->size;
			$block_to = $pos;

			// no starts positions don't count as all
			if(!count($this->starts))
			{
				return FALSE;
			}

			// test all start positions
			foreach($this->starts as $spos)
			{
				// Before? fail, return FALSE
				if($spos < $block_from)
				{
					return FALSE;
				}

				// After? fail, return FALSE
				if($block_to < $spos)
				{
					return FALSE;
				}
			}

			// no failed, return TRUE
			return TRUE;
		}

		/**
		 * filter out all startpositions that don't make this position black
		 *
		 * @param integer $pos position to force the block to contain
		 *
		 * @return integer number of changes
		 **/
		function force_to(int $pos) : int
		{
			// count changes
			$changed = 0;

			// pos in [start, end] = [start, start + length - 1] =>
			// start in [pos - length + 1, pos]
			$block_from = $pos + 1 - $this->size;
			$block_to = $pos;

			// test all start positions
			foreach($this->starts as $index => $spos)
			{
				// Before? remove
				if($spos < $block_from)
				{
					unset($this->starts[$index]);
					$changed++;
				}
				// After? remove
				else if($block_to < $spos)
				{
					unset($this->starts[$index]);
					$changed++;
				}
			}

			// Return number of changes
			return $changed;
		}

		/**
		 * textual descriptions of the stored data
		 *
		 * @return string textual description
		 **/
		function to_s() : string
		{
			return "Block size: {$this->size}, start range: " . $this->min_start() . ' - ' . $this->max_start() . ", starts: " . implode(', ', $this->starts);
		}
	}
