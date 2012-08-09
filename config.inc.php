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
	!empty($_SERVER['SCRIPT_FILENAME']) && 'comm.php' != basename($_SERVER['SCRIPT_FILENAME'])
										&& 'player.php' != basename($_SERVER['SCRIPT_FILENAME'])
										&& 'index.php' != basename($_SERVER['SCRIPT_FILENAME'])
	) die ('Loading this page directly will bring dishonor to your family.');

	//Config String Start
	$json_settings = '{"MINECRAFT_PATH":".\/minecraft","SERVER_PASS":"","LOG_LINES":40,"CENTRAL_UPDATE":false,"UPDATE_SELF":false,"UPDATE_MINECRAFT":true,"MINECRAFT_BIN":"minecraft-server.jar","NETDEV_PATH":"\/proc\/net\/dev","CPU_PATH":"\/proc\/cpuinfo","LOAD_PATH":"\/proc\/loadavg","MONITOR_HANDLE":"","JAVA_PATH":"java\/"}';
	//Config String End

	$json_array = json_decode($json_settings,true);
	foreach($json_array as $key => $value)
	{
		switch($key)
		{
			case "MINECRAFT_PATH":
				define($key,$_SERVER["DOCUMENT_ROOT"]."/".$value);
			break;

			case "JAVA_PATH":
				if($value != "")
					define($key,$_SERVER["DOCUMENT_ROOT"]."/".$value);
			break;

			default:
				define($key,$value);
			break;
		}

	}

?>
