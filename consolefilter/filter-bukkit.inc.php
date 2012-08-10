<?php

	//Remove any lines that don't start with a timestamp
	if(preg_match("/\d{2}:\d{2}:\d{2}/", $line, $match) == 0)
		$line = "";

	//Join
	if(preg_match("/\[INFO\]\s+(\S+)\s+\[\/(.*)\] logged in with entity id (\d+) at \(\[(\S*)\] (-?\d+\.\d+), (-?\d+\.\d+), (-?\d+\.\d+)\)/", $line, $match) == 1)
	{
		list($matchstring,$name,$address,$entity,$world,$X,$Y,$Z) = $match;

		$line = str_replace($matchstring,"[CONNECT] ".$name." logged into ".$world." at (".round($X).",".round($Y).",".round($Z).")",$line);

	}
	if(preg_match("/\[INFO\]\s+(\S+)\s+joined with: \[(.*)\]/", $line, $match) == 1)
	{
		$mods = explode(",",$match[2]);
		$line = str_replace($match[0],"[INFO] ".$match[1]." has ".count($mods)." mods installed.",$line);
	}
	if(preg_match("/\[INFO\]\s+Sending serverside check to:\s+(\S+)/", $line, $match) == 1)
		$line = "";
	if(preg_match("/\[INFO\]\s+Connection reset/", $line, $match) == 1)
		$line = "";

	//Leave
	if(preg_match("/\[INFO\]\s+(\S+)\s+lost connection: (.*)/", $line, $match) == 1)
	{
		switch($match[2])
		{
			case "disconnect.endOfStream":
				$reason = " left because of a lost connection";
			break;

			case "disconnect.quitting":
				$reason = " quit";
			break;

			default:
				$reason = " left because of an unknown reason (".$match[2].")";
			break;
		}

		$line = str_replace($match[0],"[DISCONNECT] ".$match[1].$reason.".",$line);
	}

?>
