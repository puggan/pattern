<?php

	class cli
	{
		const FG_WHITE = '1;37';
		const FG_BLACK = '0;30';
		const BG_BLACK = 40;
		const BG_WHITE = 47;
		const BG_BLUE = 44;
		const BG_YELLOW = 43;
		const BG_RED = 41;

		const ALIGN_LEFT = 0;
		const ALIGN_RIGHT = 1;
		const ALIGN_CENTER = 2;

		public $width;
		public $height;
		public $x_pos;
		public $y_pos;
		public $last_bg = cli::BG_BLACK;
		public $last_fg = cli::FG_WHITE;

		function __construct()
		{
			$this->fetch_size();
			$this->clear();
		}

		function fetch_size()
		{
			$this->width = exec("tput cols");
			$this->height = exec("tput lines");
		}

		function clear()
		{
			// echo "\033[2J";
			echo "\033[2J\033[H";
			$this->x_pos = 1;
			$this->y_pos = 1;
		}

		function set_position($y, $x)
		{
			if($x or $y)
			{
				echo "\033[{$y};{$x}H";
				$this->x_pos = $x;
				$this->y_pos = $y;
			}
			else
			{
				echo "\033[H";
				$this->x_pos = 1;
				$this->y_pos = 1;
			}
		}

		function set_color(int $bg_color = cli::BG_BLACK, string $fg_color = cli::FG_WHITE, bool $force = FALSE)
		{
			if($bg_color AND ($force OR $bg_color != $this->last_bg))
			{
				echo "\033[{$bg_color}m";
				$this->last_bg = $bg_color;
			}

			if($fg_color AND ($force OR $fg_color != $this->last_fg))
			{
				echo "\033[{$fg_color}m";
				$this->last_fg = $fg_color;
			}
		}

		function print(string $string)
		{
			$rows = explode("\n", $string);
			while($rows)
			{
				$row = array_shift($rows);
				$length = mb_strlen($row);
				$left_of_screen = $this->width - $this->x_pos;
				while($length >= $left_of_screen)
				{
					echo mb_substr($row, 0, $left_of_screen) . PHP_EOL;
					$this->x_pos = 1;
					$this->y_pos++;
					$string = mb_substr($row, $left_of_screen);
					$left_of_screen = $this->width;
					$length = mb_strlen($row);
				}
				if($length)
				{
					echo $row;
					$this->x_pos += $length;
				}
				if($rows)
				{
					echo "\n";
					$this->x_pos = 1;
					$this->y_pos++;
				}
			}
		}

		function print_at($raw_string, $y, $x, $width = NULL, $align = cli::ALIGN_LEFT)
		{
			foreach(explode("\n", $raw_string) as $string)
			{
				$length = mb_strlen($string);
				if(!$width)
				{
					$width = $this->width - $x;
				}
				while($length > $width)
				{
					$this->set_position($y, $x);
					echo mb_substr($string, 0, $width);
					$string = mb_substr($string, $width);
					$y++;
					$length = mb_strlen($string);
				}
				switch($align)
				{
					case cli::ALIGN_RIGHT:
					{
						$row_x = $x + ($width - $length);
						break;
					}
					case cli::ALIGN_CENTER:
					{
						$row_x = $x + round(( $width - $length) / 2);
						break;
					}
					case cli::ALIGN_LEFT:
					default:
					{
						$row_x = $x;
						break;
					}
				}
				$this->set_position($y, $row_x);
				$this->print($string);
				$y++;
			}
		}

		function nl()
		{
			echo PHP_EOL;
			$this->y_pos++;
			$this->x_pos = 1;
		}

		function ask(string $question)
		{
			$this->print($question . ' ');
			return fgets(STDIN);
		}
	}