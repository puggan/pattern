<?php

	require_once("pattern_cli.class.php");

	$cli = new pattern_cli();
	if(!empty($argv[1]))
	{
		if($argv[1] == '-')
		{
			$cli->pattern = pattern::load("php://stdin");
		}
		else
		{
			$cli->pattern = pattern::load($argv[1]);
		}
	}
	if(!empty($argv[2]))
	{
		while($cli->pattern->sync())
		{
			usleep(100000);
			$cli->print_pattern();
		}
		$cli->pattern->save($argv[2]);
		if(!empty($argv[3]))
		{
			$cli->pattern->export_png($argv[3]);
		}
	}
	else
	{
		$cli->run();
	}
