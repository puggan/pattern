<?php

	// class for a black block in a black chain
	class black_block
	{
		// the length of the block
		public $size;

		// list of posible start positions
		public $starts;

		/**
		 * Create a black block
		 * @param $size the length of the block
		 * @param $min_start first posible start location
		 * @param $max_start last posible start location
		 */
		function __construct($size, $min_start, $max_start)
		{
			// Store size/length in object
			$this->size = $size;

			// create a list from min_start to max_start
			$starts = range($min_start, $max_start);

			// use values as keys, and store as posible start locations
			$this->starts = array_combine($starts, $starts);
		}

		// Return the lowest posible start location
		function min_start()
		{
			if($this->starts)
			{
				return min($this->starts);
			}
		}

		// Return the highest posible start location
		function max_start()
		{
			if($this->starts)
			{
				return max($this->starts);
			}
		}

		// Return the lowest posible end location (start + length - 1)
		function min_end()
		{
			return $this->size -1 + $this->min_start();
		}

		// Return the highest posible end location (start + length - 1)
		function max_end()
		{
			return $this->size -1 + $this->max_start();
		}

		/**
		 * filter startpositions by black mark
		 * @param $pos the position that have the black mark
		 * @return TRUE if changes was made
		 */
		function mark_black($pos)
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
		 * @param $pos the position that have the white mark
		 * @return TRUE if changes was made
		 */
		function mark_white($pos)
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
		 * @param $new_pos new lowest starting location
		 * @return TRUE if changed
		 */
		function new_min_start($new_pos)
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
		 * @param $new_pos new highest starting location
		 * @return TRUE if changed
		 */
		function new_max_start($new_pos)
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
		 * @param $new_pos new lowest emd location
		 * @return TRUE if changed
		 */
		function new_min_end($new_pos)
		{
			// Calculate new lowest start position (end = start + length - 1)
			return $this->new_min_start($new_pos + 1 - $this->size);
		}

		/**
		 * Set a new highest end position
		 * @param $new_pos new highest emd location
		 * @return TRUE if changed
		 */
		function new_max_end($new_pos)
		{
			// Calculate new highest start position (end = start + length - 1)
			return $this->new_max_start($new_pos + 1 - $this->size);
		}

		/**
		 * Chechk if this block can be at a given position
		 * @param $pos the position to check
		 * @return TRUE if any startposition makes the given position black
		 */
		function can_be($pos)
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
		 * Chechk where this block can start given a black position
		 * @param $pos the black position to check
		 * @return list of start positions
		 */
		function can_be_starts($pos)
		{
			// empty list of start positions
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
		 * Chechk if this block most be at a given position
		 * @param $pos the position to check
		 * @return TRUE if all startposition makes the given position black
		 */
		function most_be($pos)
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
		 * @param $pos position to force the block to contain
		 * @return number of changes
		 **/
		function force_to($pos)
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
		 * @return textual description
		 **/
		function to_s()
		{
			return "Block size: {$this->size}, start range: " . $this->min_start() . ' - ' . $this->max_start() . ", starts: " . implode(', ', $this->starts);
		}
	}

	// class for handeling a chain of black blocks
	class black_chain
	{
		// total width of the white and black blocks
		public $width;
		// list of black blocks
		public $chain;
		// 2D list of black positions, and a list of block numbers that it can belong to
		public $black;

		/**
		 * Create a chain of black blocks
		 * @param $sizes list of the sizes of the blocks in the chain
		 * @param total width to place the blocks on
		 **/
		function __construct($sizes, $width)
		{
			// store total width
			$this->width = (int) $width;

			// create an empty list for black blocks
			$this->chain = array();

			// create an empty 2D list for black positions
			$this->black = array();

			// calculate the minimum width (1 white between each black = count - 1)
			$min_width = array_sum($sizes) + count($sizes) - 1;

			// calculate unknown whites
			$width_diff = $width - $min_width;

			// variable for lowest start position
			$pos = 1;

			// add the blocks, from the list of sizes
			foreach($sizes as $current_size)
			{
				// add a block somwhere from current lowest startposition to highest start position (lowest + unknown white)
				$this->chain[] = new black_block($current_size, $pos, $pos + $width_diff);

				// move lowest start position, add current size of black, and a minimum size of white at 1
				$pos += $current_size + 1;
			}
		}

		/**
		 * synca given block with its neighbours
		 * @param $index block number
		 * @return TRUE if changes was made
		 **/
		function sync($index)
		{
			// check for incorrect chain number
			if(!isset($this->chain[$index]))
			{
				return FALSE;
			}

			// sync left
			$left = $this->sync_left($index);

			// sync right
			$right = $this->sync_right($index);

			// return TRUE if left or right sync made changes
			return $left OR $right;
		}

		/**
		 * synca given block with its left most neighbours
		 * @param $index block number
		 * @return TRUE if changes was made
		 **/
		function sync_left($index)
		{
			// check for incorrect chain number
			if(!isset($this->chain[$index]))
			{
				return FALSE;
			}

			// if there is no block to the left of the current, fail
			if(!isset($this->chain[$index - 1]))
			{
				return FALSE;
			}

			// set the left blocks max end to be as close as posible to the current blocks max start
			$changes = $this->chain[$index - 1]->new_max_end($this->chain[$index]->max_start() - 2);

			// if changed, sync next left
			if($changes)
			{
				$this->sync_left($index - 1);
			}

			// return TRUE if changed
			return $changes;
		}

		/**
		 * synca given block with its right most neighbours
		 * @param $index block number
		 * @return TRUE if changes was made
		 **/
		function sync_right($index)
		{
			// check for incorrect chain number
			if(!isset($this->chain[$index]))
			{
				return FALSE;
			}

			// if there is no block to the right of the current, fail
			if(!isset($this->chain[$index + 1]))
			{
				return FALSE;
			}

			// set the right blocks min start to be as close as posible to the current blocks min end
			$changes = $this->chain[$index + 1]->new_min_start($this->chain[$index]->min_end() + 2);

			// if changed, sync next right
			if($changes)
			{
				$this->sync_right($index + 1);
			}

			// return TRUE if changed
			return $changes;
		}

		/**
		 * sync blocks according to the forced black marks
		 **/
		function sync_black($deep = FALSE)
		{
			// count changes
			$changed = 0;

			// for each black position
			foreach($this->black as $pos => $list)
			{
				// for each block that can be at this position
				foreach($list as $index)
				{
					// remove block that no longer can be at this position
					if(!$this->chain[$index]->can_be($pos))
					{
						unset($this->black[$pos][$index]);
					}
				}

				// if only one block can be at this position
				if(count($this->black[$pos]) == 1)
				{
					// for that block
					$index = max($this->black[$pos]);

					// force it to be at this position
					if($this->chain[$index]->force_to($pos))
					{
						// if changes was made, sync and count
						$this->sync($index);
						$changed++;
					}
				}
				// if there is more then one posible, and this is a deep analys
				else if($deep AND count($this->black[$pos]))
				{
					$min_index = min($this->black[$pos]);
					$max_index = max($this->black[$pos]);

					$this->chain[$min_index]->new_max_start($pos);
					$this->chain[$max_index]->new_min_end($pos);

					$max_start = 1;
					$min_end = $this->width;

					foreach($list as $index)
					{
						$current_length = $this->chain[$index]->size;
						$start_positions = $this->chain[$index]->can_be_starts($pos);

						if($start_positions)
						{
							$max_start = max($max_start, max($start_positions));
							$min_end = min($min_end, min($start_positions) + $current_length - 1);
						}
					}

					if($min_index)
					{
						foreach(range(0, $min_index - 1) as $index)
						{
							$this->chain[$index]->new_max_end($max_start - 2);
							$this->sync($index);
						}
					}

					if($max_index < count($this->chain[$index]) - 1)
					{
						foreach(range($max_index + 1, count($this->chain[$index]) - 1) as $index)
						{
							$this->chain[$index]->new_min_start($min_end + 2);
							$this->sync($index);
						}
					}

					if($max_start < $pos)
					{
						foreach(range($max_start, $pos -1) as $bpos)
						{
							if(!isset($this->black[$bpos]))
							{
								$this->mark_black($bpos);
								$changed++;
							}
						}
					}

					if($pos < $min_end)
					{
						foreach(range($pos + 1, $min_end) as $bpos)
						{
							if(!isset($this->black[$bpos]))
							{
								$this->mark_black($bpos);
								$changed++;
							}
						}
					}

				}
			}

			// return number of changes
			return $changed;
		}

		/**
		 * mark a position in chain black
		 * @param @pos position to mark as black
		 * @return NULL
		 **/
		function mark_black($pos)
		{
			// count changes
			$changes = 0;

			// add a empty list of block numbers, for this black position
			$this->black[$pos] = array();

			// foreach block in the chain
			foreach($this->chain as $block_index => $current_block)
			{
				// mark this position as black in that block
				if($current_block->mark_black($pos))
				{
					// sync block if this white mark made changes
					$this->sync($block_index);

					// count changes
					$changes++;
				}

				// can this black mark be part of this block?
				if($current_block->can_be($pos))
				{
					// add it to the list
					$this->black[$pos][$block_index] = $block_index;
				}
			}

			ksort($this->black);

			// sync the blocks according to the updated list of black positions
			while($this->sync_black(FALSE))
			{
				$changes++;
			}

			// return number of changes
			return $changes;
		}

		/**
		 * mark a position in chain white
		 * @param @pos position to mark as white
		 * @return number of changes
		 **/
		function mark_white($pos)
		{
			// count changes
			$changes = 0;

			// foreach block in the chain
			foreach($this->chain as $block_index => $current_block)
			{
				// mark this position as white in that block
				if($current_block->mark_white($pos))
				{
					// sync block if this white mark made changes
					$this->sync($block_index);

					// count changes
					$changes++;
				}
			}

			// if changed
			if($changes)
			{
				// sync the blocks according to the removed posible placements
				while($this->sync_black(FALSE))
				{
					$changes++;
				}
			}

			// return number of changes
			return $changes;
		}

		function to_keys()
		{
			while($this->sync_black(TRUE));

			$keys = array_fill(1, $this->width, 0);
			foreach($this->black as $pos => $list)
			{
				$keys[$pos] = 1;
			}
			foreach($this->chain as $current_block)
			{
				foreach(range($current_block->min_start(), $current_block->max_end()) as $pos)
				{
					if(isset($keys[$pos]) AND $keys[$pos] == 0)
					{
						if($current_block->can_be($pos))
						{
							if($current_block->most_be($pos))
							{
								$keys[$pos] = 1;
							}
							else
							{
								unset($keys[$pos]);
							}
						}
					}
				}
			}
			ksort($keys);
			return $keys;
		}

		/**
		 * textual descriptions of the stored data
		 * @return textual description
		 **/
		function to_s()
		{
			$parts = array();
			foreach($this->chain as $block_index => $current_block)
			{
				$parts[$block_index] = $current_block->to_s();
			}
			return implode("\n", $parts);
		}

		function __clone()
		{
			foreach($this->chain as $index => $black_block)
			{
				$this->chain[$index] = clone $black_block;
			}
		}
	}
