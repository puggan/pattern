<?php

	require_once("pattern.class.php");
	require_once("cli.class.php");

	class pattern_cli
	{
		/** @var cli  */
		public $cli;
		/** @var pattern */
		public $pattern;

		/** @var float 1.0 for fully solved */
		public $solved = 0.0;
		public $missing = 0;

		function __construct()
		{
			$this->cli = new cli();
		}

		function run()
		{
			$this->clear();

			if(!$this->pattern)
			{
				if($this->ask_new())
				{
					$this->ask_clues();
				}
				else
				{
					$this->ask_main_menu();
				}
			}

			while(TRUE)
			{
				while($this->pattern)
				{
					$this->print_pattern();
					while($this->pattern->sync())
					{
						usleep(100000);
						$this->print_pattern();
					}
					$this->ask_game_menu();
				}

				$this->ask_main_menu();
			}
		}

		function clear()
		{
			$this->cli->clear();

			$s = <<<TEXT_BLOCK
Welcome to Puggans Pattern-solver

TEXT_BLOCK;

			$this->cli->print($s);
		}

		function ask_new()
		{
			$game_width = (int) $this->cli->ask("How wide is your pattern?");
			if($game_width)
			{
				$game_height = (int) $this->cli->ask("How high is your pattern? [{$game_width}]");
				if(!$game_height)
				{
					$game_height = $game_width;
				}

				$this->pattern = new pattern($game_width, $game_height);
				return TRUE;
			}
		}

		function ask_clues()
		{
			foreach(range(1, $this->pattern->width) as $col_nr)
			{
				$clues = $this->cli->ask("Clues for column {$col_nr}:");
				if($clues)
				{
					$this->pattern->set_column_clue($col_nr, explode(' ', $clues));
				}
				else
				{
					return FALSE;
				}
			}
			foreach(range(1, $this->pattern->height) as $row_nr)
			{
				$clues = $this->cli->ask("Clues for row {$row_nr}:");
				if($clues)
				{
					$this->pattern->set_row_clue($row_nr, explode(' ', $clues));
				}
				else
				{
					return FALSE;
				}
			}
			return TRUE;
		}

		function ask_main_menu()
		{
			if($this->pattern)
			{
				$s = <<<TEXT_BLOCK
Congratulations.
What to do next?
 * (S)ave
 * Export as (P)NG
 * (N)ew
 * (L)oad
 * (Q)uit

TEXT_BLOCK;
			}
			else
			{
				$s = <<<TEXT_BLOCK
Congratulations.
What to do next?
 * (N)ew
 * (L)oad
 * (Q)uit

TEXT_BLOCK;
			}
			$this->cli->print($s);
			$option = $this->cli->ask("Select option: ");
			switch(strtolower(substr($option, 0, 1)))
			{
				case 'n':
				{
					if($this->ask_new())
					{
						$this->ask_clues();
					}
					return TRUE;
				}

				case 'l':
				{
					$filename = trim($this->cli->ask("file path? [saved.json]")) ?: 'saved.json';
					$this->pattern = pattern::load($filename);
					return TRUE;
				}

				case 's':
				{
					$filename = trim($this->cli->ask("file path? [saved.json]")) ?: 'saved.json';
					$this->pattern->save($filename);
					return TRUE;
				}

				case 'p':
				{
					$filename = trim($this->cli->ask("file path? [saved.png]")) ?: 'saved.png';
					$this->pattern->export_png($filename);
					return TRUE;
				}

				case 'q':
				{
					die(PHP_EOL);
				}

				default:
				{
					return FALSE;
				}
			}
		}

		function ask_game_menu()
		{
			$s = <<<TEXT_BLOCK
What to do next?
 * Mark (B)lack
 * Mark (W)hite
 * Change (C)lue
 * (R)epaint
 * (S)ave 
 * (Q)uit

TEXT_BLOCK;

			$area = $this->pattern->width * $this->pattern->height;
			$this->cli->print(sprintf("Solved %d of %d, just %d to go (%.2f%%)\n\n", $area - $this->missing, $area, $this->missing, 100 * $this->solved));
			if($this->solved AND !$this->missing)
			{
				return $this->ask_main_menu();
			}
			$this->cli->print($s);
			$option = $this->cli->ask("Select option: ");
			switch(strtolower(substr($option, 0, 1)))
			{
				case 'q':
				{
					die(PHP_EOL);
				}

				case 's':
				{
					// $this->pattern->sync();
					return TRUE;
				}

				case 'b':
				{
					$row_nr = $this->cli->ask("Row? ");
					$col_nr = $this->cli->ask("Column? ");
					$this->pattern->mark_black($col_nr, $row_nr);
					$this->pattern->sync();
					return TRUE;
				}

				case 'w':
				{
					$row_nr = $this->cli->ask("Row? ");
					$col_nr = $this->cli->ask("Column? ");
					$this->pattern->mark_white($col_nr, $row_nr);
					$this->pattern->sync();
					return TRUE;
				}

				case 'c':
				{
					$type = $this->cli->ask("(R)ow or (C)olumn");
					$type = strtolower(substr($type, 0, 1));
					if($type == 'c')
					{
						$col_nr = $this->cli->ask("Column? ");
						$current_clue = $this->pattern->columns[$col_nr]->clue;
					}
					else if($type == 'r')
					{
						$row_nr = $this->cli->ask("Row? ");
						$current_clue = $this->pattern->rows[$row_nr]->clue;
					}
					else
					{
						return FALSE;
					}

					$current_clue = implode(' ', $current_clue);
					$clue = $this->cli->ask("New clue? [{$current_clue}]");

					if($clue)
					{
						$this->pattern->set_clue($type, $col_nr ?? $row_nr, explode(' ', $clue));
					}

					return TRUE;
				}
				case 'r':
				default:
				{
					return TRUE;
				}
			}
		}

		function print_pattern()
		{
			$this->clear();
			$solved = 0;
			$missing = 0;

			$min_clue_height = 1;
			$cell_width = 3;

			foreach($this->pattern->columns as $clue)
			{
				$min_clue_height = max($min_clue_height, count($clue->clue));
			}

			$row_clue_width = $this->cli->width - $this->pattern->width * $cell_width - 4;

			if($row_clue_width > 10 AND $min_clue_height + $this->pattern->height + 5 < $this->cli->height)
			{
				foreach($this->pattern->columns as $col_nr => $clue)
				{
					$this->cli->print_at(implode("\n", $clue->clue), $min_clue_height - count($clue->clue) + 1, $row_clue_width + $col_nr * $cell_width, 2, cli::ALIGN_RIGHT);
				}

				foreach($this->pattern->columns as $row_nr => $clue)
				{
					$this->cli->print_at(implode(" ", $clue->clue), $min_clue_height + $row_nr, 1, $row_clue_width, cli::ALIGN_RIGHT);
				}
			}
			else
			{
				$cell_width = 1;
				$min_clue_height = 0;
				$row_clue_width = -2;
			}

			$colors = array(-1 => cli::BG_BLACK, 0 => cli::BG_WHITE, 1 => cli::BG_BLUE);

			$default_row = array_fill_keys(range(1, $this->pattern->width), -1);
			foreach($this->pattern->get_rows() as $row_nr => $keys)
			{
				$keys += $default_row;
				ksort($keys);
				$this->cli->set_position($min_clue_height + $row_nr, $row_clue_width + 3);
				foreach($keys as $col_nr => $value)
				{
					$color = $colors[$value] ?? cli::BG_BLACK;
					$this->cli->set_color($color);
					$this->cli->print($cell_width == 1 ? ' ' : '   ');
					if($value >= 0)
					{
						$solved++;
					}
					else
					{
						$missing++;
					}
				}
			}

			$this->solved = $solved / (($solved + $missing) ?: 1);
			$this->missing = $missing;
			$this->cli->set_color();
			$this->cli->set_position($min_clue_height + $this->pattern->height + 4, 1);
		}
	}