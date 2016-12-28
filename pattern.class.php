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
		 */
		function __construct(integer $size, integer $min_start, integer $max_start)
		{
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
		 * @param integr $pos the position that have the black mark
		 *
		 * @return boolean TRUE if changes was made
		 */
		function mark_black(integer $pos) : boolean
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
		function mark_white(integer $pos) : boolean
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
		function new_min_start(integer $new_pos) : boolean
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
		function new_max_start(integer $new_pos) : boolean
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
		function new_min_end(integer $new_pos) : boolean
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
		function new_max_end(integer $new_pos) : boolean
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
		function can_be(integer $pos) : boolean
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
		function can_be_starts(integer $pos) : array
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
		function most_be(integer $pos) : boolean
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
		function force_to(integer $pos) : integer
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

	/**
	 * class for handeling a chain of black blocks
	 */
	class black_chain
	{
		/** @var int total width of the white and black blocks */
		public $width;
		/** @var black_block[] list of black blocks */
		public $chain;
		/** @var integer[][] 2D list of black positions, and a list of block numbers that it can belong to */
		public $black;

		/**
		 * Create a chain of black blocks
		 *
		 * @param integer[] $sizes list of the sizes of the blocks in the chain
		 * @param integer $width total width to place the blocks on
		 **/
		function __construct($sizes, integer $width)
		{
			// store total width
			$this->width = $width;

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
		 * sync a given block with its neighbours
		 *
		 * @param integer $index block number
		 *
		 * @return boolean TRUE if changes was made
		 **/
		function sync(integer $index) : boolean
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
		 *
		 * @param integer $index block number
		 *
		 * @return boolean TRUE if changes was made
		 **/
		function sync_left(integer $index) : boolean
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
		 * sync a given block with its right most neighbours
		 *
		 * @param integer $index block number
		 *
		 * @return boolean TRUE if changes was made
		 **/
		function sync_right(integer $index) : boolean
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
		 *
		 * @param boolean $deep sync all blocks at each black position vs sync only single blocks
		 *
		 * @return integer number of changes
		 **/
		function sync_black(boolean $deep = FALSE) : integer
		{
			// count changes
			$changed = 0;

			// for each black position
			foreach($this->black as $pos => $list)
			{
				// filter the list from blocks no longer avaible
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
				// if there is more then one posible
				else if(count($this->black[$pos]))
				{
					// get the lowest and highest posible block
					$min_index = min($this->black[$pos]);
					$max_index = max($this->black[$pos]);

					// as there can be no block before the lowest block, the lowest must have startead here or before
					$this->chain[$min_index]->new_max_start($pos);
					$this->sync($min_index);

					// as there can be no block after the highest block, the highest must end here or later
					$this->chain[$max_index]->new_min_end($pos);
					$this->sync($max_index);

					// this is a deep analys
					if($deep)
					{
						// the unknown block at this position, where can it start and end?
						// set default values to start and end of the hole row
						$max_start = 1;
						$min_end = $this->width;

						// for all blocks that can be at the current black position
						foreach($list as $index)
						{
							// fetch all posible start_location for this block, that results in the current block is black
							$start_positions = $this->chain[$index]->can_be_starts($pos);

							// if this block still can be at this position
							if($start_positions)
							{
								// update the unknown blocks interval to match the current tested block
								$max_start = max($max_start, max($start_positions));
								$min_end = min($min_end, min($start_positions) + $this->chain[$index]->size - 1);
							}
						}

						// if the unknown block needs to start before the current position
						if($max_start < $pos)
						{
							// loop through all position that's required to be black before this block
							foreach(range($max_start, $pos - 1) as $bpos)
							{
								// if its not already marked as black
								if(!isset($this->black[$bpos]))
								{
									// mark it as black
									$this->mark_black($bpos);
									$changed++;
								}
							}
						}

						// if the unknown block needs to end after the current position
						if($pos < $min_end)
						{
							// loop through all position that's required to be black after this block
							foreach(range($pos + 1, $min_end) as $bpos)
							{
								// if its not already marked as black
								if(!isset($this->black[$bpos]))
								{
									// mark it as black
									$this->mark_black($bpos);
									$changed++;
								}
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
		 *
		 * @param integer $pos position to mark as black
		 *
		 * @return integer number of changes
		 **/
		function mark_black(integer $pos) : integer
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
					// sync block if this black mark made changes
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
		 *
		 * @param integer $pos position to mark as white
		 *
		 * @return integer number of changes
		 **/
		function mark_white(integer $pos) : integer
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

		/**
		 * Convert to a list of known white and known black
		 *
		 * @return integer[] list of position->value where value=0 for white, and value=1 for black
		 */
		function to_keys()
		{
			// make sure all black are totaly synced
			while($this->sync_black(TRUE))
			{
				;
			}

			// start with all as known white
			$keys = array_fill(1, $this->width, 0);

			// for each black, mark as known black
			foreach($this->black as $pos => $list)
			{
				$keys[$pos] = 1;
			}

			// for each block
			foreach($this->chain as $current_block)
			{
				// for each posible positions (min_start -> max_end)
				foreach(range($current_block->min_start(), $current_block->max_end()) as $pos)
				{
					// if marked as the default: known white
					if(isset($keys[$pos]) AND $keys[$pos] == 0)
					{
						// and it can be black?
						if($current_block->can_be($pos))
						{
							// and it must be black?
							if($current_block->most_be($pos))
							{
								// mark as known black
								$keys[$pos] = 1;
							}
							else
							{
								// mark as unknown
								unset($keys[$pos]);
							}
						}
					}
				}
			}
			// make sure that the keys are in numeric order (TODO: nessesary?)
			ksort($keys);
			return $keys;
		}

		/**
		 * textual descriptions of the stored data
		 * @return string textual description
		 **/
		function to_s() : string
		{
			$parts = array();

			foreach($this->chain as $block_index => $current_block)
			{
				$parts[$block_index] = $current_block->to_s();
			}

			return implode("\n", $parts);
		}

		/**
		 * make sure the clone also clones the chain
		 */
		function __clone()
		{
			foreach($this->chain as $index => $black_block)
			{
				$this->chain[$index] = clone $black_block;
			}
		}
	}
