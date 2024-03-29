<?php

	class pattern_clue
	{
		/** @var black_chain */
		public $chain;
		/** @var int the total size, white and black */
		public $size;
		/** @var integer[] list of lengths of the black blocks */
		public $clue;
		/** @var integer[] position->value, value 0 for white, value 1 for black, null for unknown */
		public $keys;

		/**
		 * pattern_clue constructor.
		 *
		 * @param int $size
		 */
		function __construct(int $size)
		{
			$this->size = $size;
			$this->keys = array();
		}

		/**
		 * @param integer[] $sizes list of lengths of the black blocks
		 *
		 * @throws Exception from black_chain->__construct()
		 *
		 * @return boolean;
		 */
		function set_clue($sizes) : bool
		{
			$this->clue = array();
			foreach($sizes as $next_size)
			{
				$this->clue[] = (int) $next_size;
			}
			$this->clue = array_filter($this->clue);
			$this->chain = new black_chain($this->clue, $this->size);
			if(isset($this->chain->chain[0]) AND $this->chain->chain[0]->starts)
			{
				$this->keys = $this->chain->to_keys();
				return TRUE;
			}
			return FALSE;
		}

		/**
		 * Update this row/column according to the new keys marked withe and black positions
		 *
		 * @param integer[] $new_key
		 *
		 * @return integer
		 */
		function update_key($new_key) : int
		{
			$changes = 0;

			foreach($new_key as $position => $value)
			{
				if(!isset($this->key[$position]))
				{
					if($value)
					{
						if($this->chain->mark_black($position))
						{
							$changes++;
						}
					}
					else
					{
						if($this->chain->mark_white($position))
						{
							$changes++;
						}
					}
				}
			}

			if($changes)
			{
				$this->keys = $this->chain->to_keys();
			}

			return $changes;
		}
	}