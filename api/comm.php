<?php

	/*
	Mincraft Monitor Webservice object: Version 0.2
	http://www.twosphere.com.au
	http://www.minecraftmonitor.com
	http://www.minecrafttrader.com
	
	All code is provided by Twosphere for testing purposes only.
	This code has not been thoroughly tested under all conditions. Twosphere, therefore, cannot guarantee or imply reliability, serviceability, or function of these programs.
	All programs contained herein are provided to you "AS IS" without any warranties of any kind.
	The implied warranties of non-infringement, merchantability and fitness for a particular purpose are expressly disclaimed.

	date: 2011-08-12
	File: comm.php
	Main entry point for all webservices and control.
	*/	
	
	define("SELF_VERSION","0.2");
	ini_set('memory_limit', '512M');

	ob_start();include("config.inc.php");ob_end_clean();
	include("helpers.inc.php");
	include("data.inc.php");

	//Check for permission to use webservice.
	include("access.inc.php");

	//Minecraft NBT files	
	require("nbt.class.php");

	//User functions
	include("functions.inc.php");

	//Prepare data and send.
	minecraft_send("list");

	//Server settings
	if(file_exists(MINECRAFT_PATH."/server.properties"))
	{
		$settings = file_get_contents(MINECRAFT_PATH."/server.properties");
		$settings = explode("\n",$settings);
		foreach($settings as $line)
		{
			//Remove comments
			$line = preg_replace("/\#.*/","",$line);

			if(strpos($line,"=") !== false)
			{
				list($param,$value) = explode("=",$line);
				$properties[$param]=$value;
			}
		}
	}

	if(isset($properties['level-name']))
	if(file_exists(MINECRAFT_PATH."/".$properties['level-name']."/level.dat"))
	{
		ob_start();
		passthru("/bin/gzip -d -c ".MINECRAFT_PATH."/".$properties['level-name']."/level.dat");
		$contents=ob_get_contents();
		ob_end_clean();
		$properties['spawn'] = minecraft_spawn($contents);
		$properties['time'] = minecraft_time($contents);
		$properties['raintime'] = minecraft_raintime($contents);
	}

	if(isset($properties['level-name']))
	if(file_exists(MINECRAFT_PATH."/ops.txt"))
	{
		//Get family.
		$ops = explode("\n",file_get_contents(MINECRAFT_PATH."/ops.txt"));
		$banned = explode("\n",file_get_contents(MINECRAFT_PATH."/banned-players.txt"));
		foreach (glob(MINECRAFT_PATH."/".$properties['level-name']."/players/*.dat") as $filepath) 
		{
			ob_start();
			passthru("/bin/gzip -d -c ".$filepath);
			$contents=ob_get_contents();
			ob_end_clean();

			$pos = minecraft_pos($contents);

			$filename = explode("/",$filepath);
			$filename = $filename[count($filename)-1];

			$playername = explode(".",$filename);
			$playername = $playername[0];
			$lastlogon = filemtime($filepath);

			$family[$playername]['logtime'] = $lastlogon;
			if(is_array($ops) && in_array(strtolower($playername),$ops)) $family[$playername]['op'] = "true";
			if(is_array($banned) && in_array(strtolower($playername),$banned)) $family[$playername]['banned'] = "true";
			$family[$playername]['pos'] = $pos;
		}
	}

	if(file_exists(NETDEV_PATH))
	{
		//Get system specs
		$devset1 = dev2array(file_get_contents(NETDEV_PATH));
		sleep(1); //Wait for the values to change.
		$devset2 = dev2array(file_get_contents(NETDEV_PATH));

		foreach($devset1 as $devname => $dev)
		{
			$rxbytes = $devset2[$devname]['rxbytes'] - $devset1[$devname]['rxbytes'];
			$txbytes = $devset2[$devname]['txbytes'] - $devset1[$devname]['txbytes'];
			$json_out['bandwidth'][$devname]['tx']=$txbytes;
			$json_out['bandwidth'][$devname]['rx']=$rxbytes;
		}
	}

	if(file_exists(CPU_PATH) && file_exists(LOAD_PATH))
	{
		$cpu = cpuinfo2array(file_get_contents(CPU_PATH));
		$load = file_get_contents(LOAD_PATH);
		$load = explode(" ", $load );
		//Load total calulated by number processors
		$cpu_load = ($load[0]/count($cpu))*100;
		$json_out['cpu'] = $cpu_load;
	}

	$memory = exec("free -m | grep Mem");
	$memory = preg_match("/Mem:\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)/",$memory,$match);	
	$json_out['memory']['phys']['t'] = $match[1];
	$json_out['memory']['phys']['u'] = $match[2] - $match[6];

	$memory = exec("free -m | grep Swap");
	$memory = preg_match("/Swap:\s+(\d+)\s+(\d+)\s+(\d+)/",$memory,$match);	
	$json_out['memory']['swp']['t'] = $match[1];
	$json_out['memory']['swp']['u'] = $match[2];


	//Get services
	$minecraft = exec("ps aux | grep ".substr(MINECRAFT_BIN,0,strlen(MINECRAFT_BIN)-1)."[".substr(MINECRAFT_BIN,-1)."]");
	if($minecraft)
	{
		$minecraft_array = array();
		preg_match("/\w+\s+(\d+)\s+(\d+\.\d+)\s+(\d+\.\d+)/",$minecraft,$minecraft_array);

		if(isset($minecraft_array[1]))
		{
			$minecraft_pid = $minecraft_array[1];
			$json_out['java_cpu'] = $minecraft_array[2];
			$json_out['java_mem'] = $minecraft_array[3];
		}
		else
			$json_out['ps_res'] = $minecraft;
		
		//$minecraft_ = explode(" ", $minecraft );
		//$minecraft_pid = $minecraft[1];
	}

	
	//Encode into JSON webservice.
	if(isset($minecraft_pid))
		$json_out['minecraft'] = "running";
	else
		$json_out['minecraft'] = "mia";



	if(file_exists(MINECRAFT_PATH."/server.mon"))
	{
		$log = array_reverse(explode("\n",file_get_contents(MINECRAFT_PATH."/server.mon")));
		$linenumber = 0;
		foreach($log as $line)
		{
			$line = str_replace(array(">list"),"",$line);
			$line = str_replace(chr(27)."[0m","",$line);
			$line = str_replace(chr(31),"",$line);
			$line = substr($line,1);

			if(trim($line) == "")
			{

			}
			else if(strpos($line,"Attempted to place a tile entity where there was no entity tile!") !== false)
			{

			}
			else if(preg_match("/Connected players:(.*)/",$line,$matches))
			{
				$playerlist = str_replace(" ","",$matches[1]);

				if($playerlist == "")
					$playerlist  = array();
				else
					$playerlist = explode(",",$playerlist);


				if(!isset($json_out['players']))
				{
					foreach($playerlist as $playername)		
					{
						$json_out['players'][] = $playername;
					}
				}
			}
			else 
			{
				$linenumber++;
				if($linenumber > LOG_LINES) break;
				$json_out['log'][]=$line;
			}
		}
		$json_out['log'] = array_reverse($json_out['log']);
	}

	
	if(isset($family))		$json_out['family'] = $family;
	if(isset($properties))	$json_out['properties'] = $properties;


	//Only send specific sections of data.
	if(isset($_GET['isolate']))
	{
		$isolate = explode(",",$_GET['isolate']);
		foreach($isolate as $value)
			if(isset($json_out[$value]))
				$prejson[$value] = $json_out[$value];

		$json_out = $prejson;
	}
	
	//Output data to client
	echo json_encode( $json_out );
?>
