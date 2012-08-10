<?php

	/*
	Mincraft Monitor Webservice object: Version 0.2
	http://www.twosphere.com.au
	http://www.minecraftmonitor.com.au

	All code is provided by Twosphere for testing purposes only.
	This code has not been thoroughly tested under all conditions. Twosphere, therefore, cannot guarantee or imply reliability, serviceability, or function of these programs.
	All programs contained herein are provided to you "AS IS" without any warranties of any kind.
	The implied warranties of non-infringement, merchantability and fitness for a particular purpose are expressly disclaimed.

	date: 2011-07-23
	File: config.inc.php
	Global configuration for webservices.
	*/

	//Security, ignore this line
	if (
	!empty($_SERVER['SCRIPT_FILENAME']) && 'index.ajax.php' != basename($_SERVER['SCRIPT_FILENAME'])
										&& 'comm.php' != basename($_SERVER['SCRIPT_FILENAME'])
										&& 'index.php' != basename($_SERVER['SCRIPT_FILENAME'])
	) die ('Loading this page directly will bring dishonor to your family.');

	//Config String Start
	$json_settings = file_get_contents("config.json");
	//Config String End

	$json_array = json_decode($json_settings,true);
	foreach($json_array as $key => $value)
	{
		switch($key)
		{
			default:
				define($key,$value);
			break;
		}

	}

?>
