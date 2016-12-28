<?php

	require_once("pattern_cli.class.php");

	$cli = new pattern_cli();
	if(!empty($argv[1]))
	{
		if($argv[1] == '-')
		{
			$cli->load("php://stdin");
		}
		else
		{
			$cli->load($argv[1]);
		}
	}
	$cli->run();